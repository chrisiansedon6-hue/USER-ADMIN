<?php
require_once 'config.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Artisan Pastries - Handcrafted Since 1985</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* --- General & Reset --- */
body { margin:0; font-family: 'Quicksand', sans-serif; color:#333; line-height:1.6; }
a { text-decoration:none; color:inherit; }
/* --- Navbar --- */
.header { position: sticky; top:0; background:#fff; z-index:1000; box-shadow:0 2px 8px rgba(0,0,0,0.05);}
.nav-container { display:flex; align-items:center; justify-content:space-between; padding:1rem 2rem; }
.nav-link { margin:0 0.5rem; font-weight:500; transition: color 0.3s;}
.nav-link:hover, .nav-link.active { color:#e17055; }
.logo { font-size:1.5rem; font-weight:bold; color:#e17055; display:flex; align-items:center; gap:0.5rem;}
.mobile-menu-btn { display:none; background:none; border:none; font-size:1.5rem; cursor:pointer; }
/* --- Dropdown --- */
.dropdown { position: relative; display:inline-block; }
.dropdown-btn { background:none; border:none; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:0.25rem; padding:0.5rem 1rem;}
.dropdown-menu { display:none; position:absolute; top:100%; right:0; background:#fff; border-radius:0.5rem; box-shadow:0 4px 12px rgba(0,0,0,0.15); min-width:140px; z-index:1000;}
.dropdown-menu a { display:block; padding:0.75rem 1rem; color:#333; font-weight:500; transition:background 0.2s;}
.dropdown-menu a:hover { background:#f3f4f6; }
.dropdown.show .dropdown-menu { display:block; }

/* --- Hero --- */
.hero { min-height:100vh; display:flex; align-items:center; justify-content:center; text-align:center; position:relative; color:#fff; overflow:hidden; background:#333;}
.hero-image img { width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; z-index:0; }
.hero-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:1; }
.hero-content { position:relative; z-index:2; padding:0 1rem; }
.hero-title { font-size:3rem; margin-bottom:0.5rem; animation:fadeInDown 1s ease forwards; }
.hero-subtitle { font-size:1.5rem; color:#fab1a0; }
.hero-text { margin:1rem 0 2rem; font-size:1.125rem; max-width:600px; margin-left:auto; margin-right:auto; }
.hero-buttons button { margin:0 0.5rem; padding:0.75rem 2rem; border-radius:0.5rem; font-weight:600; cursor:pointer; border:none; transition:all 0.3s; }
.btn-primary-large { background:#e17055; color:#fff; }
.btn-primary-large:hover { background:#fdcb6e; transform:translateY(-3px);}
.btn-secondary-large { background:#fff; color:#e17055; }
.btn-secondary-large:hover { background:#fab1a0; }
@keyframes fadeInDown {0% {opacity:0; transform:translateY(-20px);} 100% {opacity:1; transform:translateY(0);} }

/* --- Features --- */
.features { padding:4rem 2rem; background:#f9f9f9; }
.features-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:2rem; max-width:1200px; margin:0 auto; }
.feature-item { text-align:center; padding:1rem; }
.feature-icon { font-size:2rem; margin-bottom:0.5rem; color:#e17055; }
.feature-title { font-weight:bold; margin-bottom:0.5rem; }
.feature-desc { color:#6b7280; }

/* --- Menu Section --- */
.menu-section { padding:4rem 2rem; max-width:1200px; margin:0 auto; }
.menu-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:2rem; }
.menu-item { display:flex; flex-direction:column; justify-content:space-between; border:1px solid #e5e7eb; border-radius:0.5rem; overflow:hidden; transition: transform 0.3s, box-shadow 0.3s; background:#fff; min-height:420px; }
.menu-image img { width:100%; height:250px; object-fit:cover; }
.menu-info { padding:1rem; display:flex; justify-content:space-between; align-items:center; }
.menu-name { font-weight:bold; }
.menu-desc { font-size:0.875rem; color:#6b7280; }
.menu-price { font-weight:bold; color:#e17055; }
.btn-add-cart { background:#e17055; color:#fff; padding:0.5rem 1rem; border-radius:0.5rem; font-weight:600; display:inline-flex; align-items:center; gap:0.25rem; justify-content:center; transition:background 0.3s; border:none; cursor:pointer; margin-top:0.5rem; }
.btn-add-cart:hover { background:#fdcb6e; }

/* --- Responsive --- */
@media(max-width:1024px){ .menu-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:768px){ .menu-grid { grid-template-columns:1fr; } .mobile-menu-btn { display:block; } }

/* --- About --- */
.about-section { padding:4rem 2rem; background:#fff; max-width:1200px; margin:0 auto; }
.about-text { margin-bottom:1rem; color:#555; }
.about-images { display:flex; gap:1rem; margin-top:1rem; flex-wrap:wrap; }
.about-images img { flex:1; min-width:200px; border-radius:0.5rem; object-fit:cover; }

/* --- CTA --- */
.cta-section { padding:4rem 2rem; background:#e17055; color:#fff; text-align:center; }
.cta-title { font-size:2rem; margin-bottom:1rem; }
.cta-text { font-size:1.125rem; margin-bottom:2rem; }
.btn-white-large { background:#fff; color:#e17055; padding:0.75rem 2rem; border-radius:0.5rem; font-weight:600; border:none; cursor:pointer; transition:all 0.3s; }
.btn-white-large:hover { background:#fdcb6e; }

/* --- Footer --- */
.footer { background:#1f2937; color:#fff; padding:2rem; }
.footer-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:2rem; max-width:1200px; margin:0 auto; }
.footer-logo { display:flex; align-items:center; gap:0.5rem; font-size:1.25rem; font-weight:bold; color:#e17055; }
.footer-title { font-weight:bold; margin-bottom:0.5rem; }
.footer-text { margin-bottom:0.25rem; color:#d1d5db; }
.footer-bottom { text-align:center; margin-top:2rem; color:#9ca3af; }
.social-link { margin-right:0.5rem; color:#fff; font-size:1.25rem; transition:color 0.3s; }
.social-link:hover { color:#e17055; }
</style>
</head>
<body>

<!-- Header -->
<header class="header">
<nav class="nav-container">
    <a href="index.php" class="logo"><i class="fas fa-shopping-bag"></i> Artisan Pastries</a>
    <div class="nav-menu" id="navMenu">
        <a href="#menu" class="nav-link">Menu</a>
        <a href="#about" class="nav-link">About</a>
        <a href="#contact" class="nav-link">Contact</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Cart <?php echo isset($_SESSION['cart']) ? '('.count($_SESSION['cart']).')' : ''; ?></a>
            <a href="orders.php" class="nav-link">My Orders</a>

            <div class="dropdown" id="userDropdownContainer">
                <button class="dropdown-btn" id="userDropdownBtn">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="nav-link">Sign In</a>
            <a href="signup.php" class="btn-primary-large">Sign Up</a>
        <?php endif; ?>
    </div>
    <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
</nav>
</header>

<!-- Hero -->
<section class="hero">
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1736520537688-1f1f06b71605?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=1080" alt="Artisan pastries">
        <div class="hero-overlay"></div>
    </div>
    <div class="hero-content">
        <h1 class="hero-title">Handcrafted Pastries<br><span class="hero-subtitle">Since 1985</span></h1>
        <p class="hero-text">Experience the art of traditional French pastry making with our daily selection of croissants, danishes, and sweet treats.</p>
        <div class="hero-buttons">
            <button class="btn-primary-large" onclick="document.querySelector('#menu').scrollIntoView({behavior:'smooth'})">View Menu</button>
            <button class="btn-secondary-large">Visit Us</button>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
<div class="features-grid">
    <div class="feature-item"><div class="feature-icon"><i class="fas fa-clock"></i></div><h3 class="feature-title">Fresh Daily</h3><p class="feature-desc">Baked fresh every morning using traditional techniques</p></div>
    <div class="feature-item"><div class="feature-icon"><i class="fas fa-award"></i></div><h3 class="feature-title">Award Winning</h3><p class="feature-desc">Recognized for excellence in artisan pastry making</p></div>
    <div class="feature-item"><div class="feature-icon"><i class="fas fa-heart"></i></div><h3 class="feature-title">Made with Love</h3><p class="feature-desc">Each pastry crafted with care and premium ingredients</p></div>
</div>
</section>

<!-- Menu -->
<section id="menu" class="menu-section">
<h2 class="section-title">Our Signature Selection</h2>
<p class="section-subtitle">Freshly baked every morning</p>
<div class="menu-grid" id="menuGrid"></div>
<p id="noProductsMsg" style="grid-column:1/-1; text-align:center; color:#6b7280; display:none;">No products available at the moment.</p>
</section>

<!-- About -->
<section id="about" class="about-section">
<h2 class="section-title">Our Story</h2>
<p class="about-text">For over three decades, we've been bringing the authentic taste of French pastries to our community. Our master bakers start each day at 4 AM, carefully preparing every item by hand using time-honored techniques and the finest ingredients.</p>
<p class="about-text">From our flaky croissants to our delicate éclairs, each pastry tells a story of passion, tradition, and dedication to the craft.</p>
<div class="about-images"><img src="img/chef.png" alt="Our Master Baker"></div>
</section>

<!-- CTA -->
<section class="cta-section">
<h2 class="cta-title">Visit Us Today</h2>
<p class="cta-text">Open daily from 7 AM to 7 PM. Come taste the difference that passion and quality make.</p>
<button class="btn-white-large">Get Directions</button>
</section>

<!-- Footer -->
<footer id="contact" class="footer">
<div class="footer-grid">
    <div>
        <div class="footer-logo"><i class="fas fa-shopping-bag"></i> Artisan Pastries</div>
        <p class="footer-text">Crafting delicious memories since 1985</p>
    </div>
    <div>
        <h3 class="footer-title">Hours</h3>
        <p class="footer-text">Monday - Sunday</p>
        <p class="footer-text">7:00 AM - 7:00 PM</p>
    </div>
    <div>
        <h3 class="footer-title">Contact</h3>
        <p class="footer-text">123 Bakery Lane</p>
        <p class="footer-text">contact@artisanpastries.com</p>
        <p class="footer-text">(555) 123-4567</p>
    </div>
    <div>
        <h3 class="footer-title">Follow Us</h3>
        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
    </div>
</div>
<div class="footer-bottom">&copy; 2026 Artisan Pastries. All rights reserved.</div>
</footer>

<script>
// Mobile menu toggle
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');
mobileMenuBtn.addEventListener('click', () => navMenu.classList.toggle('open'));

// User dropdown toggle
<?php if(isset($_SESSION['user_id'])): ?>
const userDropdownBtn = document.getElementById('userDropdownBtn');
const userDropdownContainer = document.getElementById('userDropdownContainer');
userDropdownBtn.addEventListener('click', e => { e.stopPropagation(); userDropdownContainer.classList.toggle('show'); });
document.addEventListener('click', ()=> userDropdownContainer.classList.remove('show'));
<?php endif; ?>

async function loadProducts(){
    try{
        const res = await fetch('fetch_products.php');
        const products = await res.json();
        const menuGrid = document.getElementById('menuGrid');
        const noProductsMsg = document.getElementById('noProductsMsg');
        menuGrid.innerHTML = '';

        if(products.length === 0){
            noProductsMsg.style.display='block';
            return;
        } else {
            noProductsMsg.style.display='none';
        }

        products.forEach(p=>{
            const div = document.createElement('div');
            div.classList.add('menu-item');

            let actionBtn = '';

            <?php if(isset($_SESSION['user_id'])): ?>
                if(p.stock > 0){
                    actionBtn = `
                        <form method="POST" action="add_to_cart.php">
                            <input type="hidden" name="product_id" value="${p.id}">
                            <button type="submit" class="btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    `;
                } else {
                    actionBtn = `<button class="btn-add-cart" disabled>Out of Stock</button>`;
                }
            <?php else: ?>
                actionBtn = `
                    <a href="login.php" class="btn-add-cart">
                        <i class="fas fa-sign-in-alt"></i> Login to Order
                    </a>
                `;
            <?php endif; ?>

            div.innerHTML = `
                <div class="menu-image">
                    <img src="${p.image}" alt="${p.name}">
                </div>
                <div class="menu-info">
                    <div>
                        <h3 class="menu-name">${p.name}</h3>
                        <p class="menu-desc">${p.details}</p>
                        <small>Stock: ${p.stock}</small>
                    </div>
                    <span class="menu-price">₱${p.price}</span>
                </div>
                ${actionBtn}
            `;

            menuGrid.appendChild(div);
        });

    } catch(err){
        console.error("Error loading products:", err);
    }
}

loadProducts();
setInterval(loadProducts, 15000);
</script>
</body>
</html>