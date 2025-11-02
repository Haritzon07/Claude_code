// =====================================================
// CONFETTI EFFECT
// =====================================================

class Confetti {
    constructor() {
        this.canvas = document.getElementById('confetti-canvas');
        if (!this.canvas) {
            // Crear canvas si no existe
            this.canvas = document.createElement('canvas');
            this.canvas.id = 'confetti-canvas';
            this.canvas.style.position = 'fixed';
            this.canvas.style.top = '0';
            this.canvas.style.left = '0';
            this.canvas.style.width = '100%';
            this.canvas.style.height = '100%';
            this.canvas.style.pointerEvents = 'none';
            this.canvas.style.zIndex = '9999';
            document.body.appendChild(this.canvas);
        }

        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.animationFrame = null;

        this.resize();
        window.addEventListener('resize', () => this.resize());
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    createParticle() {
        const colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F'];

        return {
            x: Math.random() * this.canvas.width,
            y: Math.random() * this.canvas.height - this.canvas.height,
            size: Math.random() * 8 + 4,
            speedX: Math.random() * 4 - 2,
            speedY: Math.random() * 3 + 2,
            color: colors[Math.floor(Math.random() * colors.length)],
            rotation: Math.random() * 360,
            rotationSpeed: Math.random() * 10 - 5,
            opacity: 1
        };
    }

    launch(count = 150) {
        // Crear partículas
        for (let i = 0; i < count; i++) {
            this.particles.push(this.createParticle());
        }

        // Iniciar animación
        if (!this.animationFrame) {
            this.animate();
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.particles.forEach((particle, index) => {
            // Actualizar posición
            particle.x += particle.speedX;
            particle.y += particle.speedY;
            particle.rotation += particle.rotationSpeed;

            // Gravedad
            particle.speedY += 0.1;

            // Fade out
            if (particle.y > this.canvas.height - 100) {
                particle.opacity -= 0.02;
            }

            // Dibujar partícula
            this.ctx.save();
            this.ctx.translate(particle.x, particle.y);
            this.ctx.rotate((particle.rotation * Math.PI) / 180);
            this.ctx.globalAlpha = particle.opacity;

            this.ctx.fillStyle = particle.color;
            this.ctx.fillRect(-particle.size / 2, -particle.size / 2, particle.size, particle.size);

            this.ctx.restore();

            // Eliminar partícula si está fuera de la pantalla
            if (particle.y > this.canvas.height + 100 || particle.opacity <= 0) {
                this.particles.splice(index, 1);
            }
        });

        // Continuar animación si hay partículas
        if (this.particles.length > 0) {
            this.animationFrame = requestAnimationFrame(() => this.animate());
        } else {
            this.animationFrame = null;
        }
    }
}

// Instancia global
let confetti = null;

function lanzarConfetti(count = 150) {
    if (!confetti) {
        confetti = new Confetti();
    }
    confetti.launch(count);
}
