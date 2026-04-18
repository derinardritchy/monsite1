# 🎓 BiroTech v3.0 — Platfòm Aprantisaj Enfòmatik
**Kreye pa Ingénieur Derinard Ritchy**

## 📦 Tout Fichye yo (28 fichye)
```
birotech/
├── index.php              ← Akèy (logo, barre rechèch, kreateur)
├── register.php / login.php / logout.php
├── cours.php              ← Lis kou (filtre, rechèch)
├── cours_view.php         ← Kou bilenng FR/EN + bouton egzamen
├── dashboard.php          ← Tablo bò (mesaj simplifye)
├── ai_assistant.php       ← 🤖 AI Claude intègre
├── recherche.php          ← Barre rechèch kou
├── certificat.php         ← 🏆 Sètifika PDF otomatik
├── coupon.php             ← 🎟️ Sistèm koupon/promo
├── apropos.php            ← Paj À Propos + foto kreateur
├── database.sql           ← BDD konplè (IMPORTE PREMYE)
├── assets/img/
│   ├── logo.svg           ← Logo BiroTech
│   └── creator.svg        ← Foto kreateur
├── css/style.css
├── js/main.js + particles.js
├── includes/config.php
└── admin/
    ├── index.php          ← Tablo bò admin
    ├── users.php          ← 👥 Lis itilizatè + 2 bouton aksè chak
    ├── user_detail.php    ← Detay kont konplè pa itilizatè
    ├── paiements.php      ← Konfime/rejte depo
    ├── messages.php       ← Mesaj prive + repons
    └── cours_admin.php    ← Jere kou yo
```

## 🚀 Enstalasyon WAMP/XAMPP
1. Kopye nan `C:\wamp64\www\birotech\`
2. phpMyAdmin → Kreye `birotech_db` → Importe `database.sql`
3. Mete modpas admin (SQL): `UPDATE users SET password='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uye29tyyK' WHERE email='admin@birotech.ht';` → Modpas: `password`
4. `includes/config.php` → verifye DB_USER/DB_PASS
5. `ai_assistant.php` → ajoute: `const ANTHROPIC_KEY = 'sk-ant-...';`
6. Ouvri: http://localhost/birotech

## ✨ Tout Fonksyonalite v3.0
| # | Fonksyon | Estatut |
|---|---|---|
| 1 | Kou long detaye an français | ✅ |
| 2 | Egzamen bilenng FR + EN | ✅ |
| 3 | Bouton "Klike pou konpoze egzamen" | ✅ |
| 4 | 🏆 Sètifika PDF otomatik | ✅ |
| 5 | 📊 Sistèm nòt/pwogresyon | ✅ |
| 6 | 🔍 Barre rechèch kou | ✅ |
| 7 | 👤 Foto kreateur + À Propos | ✅ |
| 8 | 🖼️ Logo BiroTech SVG | ✅ |
| 9 | 🎟️ Sistèm koupon/promo | ✅ |
| 10 | 💬 Mesaj prive simplifye | ✅ |
| 11 | 🤖 AI Assistant Claude | ✅ |
| 12 | 👥 Admin: detay kont pa itilizatè | ✅ |
| 13 | ✅❌ 2 Bouton aksè individyèl | ✅ |
| 14 | 🎬 Vidéo Photoshop reyèl | ✅ |
| 15 | Mode Sombre / Solèy | ✅ |
| 16 | Responsive Android/iPhone | ✅ |

*BiroTech v3.0 — Derinard Ritchy © 2025*
