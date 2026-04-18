// ============================================
// BiroTech — Gold Particles Animation
// Boul Touwon Klere tankou Lò
// ============================================

(function () {
  const canvas = document.getElementById('particles');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let W, H, particles = [], animId;

  function resize() {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', () => { resize(); initParticles(); });

  class Particle {
    constructor() { this.reset(); }

    reset() {
      this.x = Math.random() * W;
      this.y = Math.random() * H;
      this.r = Math.random() * 4 + 1.5;
      this.baseR = this.r;
      this.vx = (Math.random() - 0.5) * 0.6;
      this.vy = (Math.random() - 0.5) * 0.6 - 0.3;
      this.life = 0;
      this.maxLife = Math.random() * 200 + 150;
      this.type = Math.random() > 0.6 ? 'gold' : (Math.random() > 0.5 ? 'blue' : 'white');
      this.alpha = 0;
      this.glowIntensity = Math.random() * 0.8 + 0.2;
      this.pulse = Math.random() * Math.PI * 2;
      this.pulseSpeed = Math.random() * 0.04 + 0.01;
    }

    update() {
      this.x += this.vx;
      this.y += this.vy;
      this.life++;
      this.pulse += this.pulseSpeed;
      this.r = this.baseR + Math.sin(this.pulse) * 0.8;

      // Fade in/out
      const half = this.maxLife / 2;
      if (this.life < half * 0.3) {
        this.alpha = (this.life / (half * 0.3)) * this.glowIntensity;
      } else if (this.life < half) {
        this.alpha = this.glowIntensity;
      } else {
        this.alpha = (1 - (this.life - half) / half) * this.glowIntensity;
      }

      if (this.life > this.maxLife || this.x < -20 || this.x > W + 20 || this.y < -20) {
        this.x = Math.random() * W;
        this.y = H + 20;
        this.vx = (Math.random() - 0.5) * 0.6;
        this.vy = -(Math.random() * 0.8 + 0.2);
        this.life = 0;
        this.type = Math.random() > 0.6 ? 'gold' : (Math.random() > 0.5 ? 'blue' : 'white');
      }
    }

    draw() {
      if (this.alpha <= 0) return;

      const colors = {
        gold: { r: 255, g: 215, b: 0 },
        blue: { r: 0, g: 176, b: 255 },
        white: { r: 200, g: 220, b: 255 }
      };
      const c = colors[this.type];

      ctx.save();
      ctx.globalAlpha = this.alpha * 0.85;

      // Glow effect
      const grd = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.r * 4);
      grd.addColorStop(0, `rgba(${c.r},${c.g},${c.b},0.9)`);
      grd.addColorStop(0.4, `rgba(${c.r},${c.g},${c.b},0.4)`);
      grd.addColorStop(1, `rgba(${c.r},${c.g},${c.b},0)`);

      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r * 4, 0, Math.PI * 2);
      ctx.fillStyle = grd;
      ctx.fill();

      // Core dot
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${c.r},${c.g},${c.b},1)`;
      ctx.shadowColor = `rgba(${c.r},${c.g},${c.b},0.9)`;
      ctx.shadowBlur = this.r * 6;
      ctx.fill();

      ctx.restore();
    }
  }

  function initParticles() {
    particles = [];
    const count = Math.min(Math.floor((W * H) / 14000), 80);
    for (let i = 0; i < count; i++) {
      const p = new Particle();
      p.life = Math.random() * p.maxLife;
      p.y = Math.random() * H;
      particles.push(p);
    }
  }

  function drawConnections() {
    const maxDist = 120;
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < maxDist) {
          const alpha = (1 - dist / maxDist) * 0.06;
          ctx.save();
          ctx.globalAlpha = alpha;
          ctx.strokeStyle = 'rgba(255,215,0,0.8)';
          ctx.lineWidth = 0.5;
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.stroke();
          ctx.restore();
        }
      }
    }
  }

  function animate() {
    ctx.clearRect(0, 0, W, H);
    drawConnections();
    particles.forEach(p => { p.update(); p.draw(); });
    animId = requestAnimationFrame(animate);
  }

  // ===== Text reveal animation =====
  function createTextBurst(x, y, count = 8) {
    for (let i = 0; i < count; i++) {
      const p = new Particle();
      p.x = x; p.y = y;
      const angle = (i / count) * Math.PI * 2;
      const speed = Math.random() * 2 + 1;
      p.vx = Math.cos(angle) * speed;
      p.vy = Math.sin(angle) * speed;
      p.r = Math.random() * 3 + 2;
      p.baseR = p.r;
      p.maxLife = 60 + Math.random() * 40;
      p.life = 0;
      p.type = 'gold';
      particles.push(p);
    }
  }

  // Burst when hero button appears
  const heroBtn = document.getElementById('startBtn');
  if (heroBtn) {
    setTimeout(() => {
      const rect = heroBtn.getBoundingClientRect();
      createTextBurst(rect.left + rect.width / 2, rect.top + rect.height / 2, 15);
    }, 1200);
  }

  initParticles();
  animate();

  // Pause on hidden tab
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) { cancelAnimationFrame(animId); }
    else { animate(); }
  });
})();
