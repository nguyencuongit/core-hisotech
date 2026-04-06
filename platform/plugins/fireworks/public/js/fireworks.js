
const canvas = document.getElementById('fireworks');
if (!canvas) {
    console.error('No fireworks canvas');
}

const ctx = canvas.getContext('2d');
let particles = [];

function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
resize();
window.addEventListener('resize', resize);

window.addEventListener('click', (e) => {
    for (let i = 0; i < 25; i++) {
        particles.push({
            x: e.clientX,
            y: e.clientY,
            vx: (Math.random() - 0.5) * 4,
            vy: (Math.random() - 0.5) * 4,
            life: 40,
            color: `hsl(${Math.random() * 360}, 100%, 60%)`
        });
    }
});

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.x += p.vx;
        p.y += p.vy;
        p.life--;

        ctx.fillStyle = p.color;
        ctx.fillRect(p.x, p.y, 3, 3);

        if (p.life <= 0) {
            particles.splice(i, 1);
        }
    }

    requestAnimationFrame(animate);
}

animate();
