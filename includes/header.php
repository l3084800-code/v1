<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="header">
    <nav class="nav">
        <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../index.php' : 'index.php'; ?>" class="logo">
            <?php echo defined('SITE_NAME') ? SITE_NAME : 'My Blog'; ?>
        </a>
        
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()" id="mobileMenuBtn">
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        
        <ul class="nav-links" id="navLinks">
            <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../index.php' : 'index.php'; ?>" 
                   class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">🏠 Home</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../chat.php' : 'chat.php'; ?>" 
                       class="<?php echo ($current_page === 'chat.php') ? 'active' : ''; ?>">💬 Chat</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../auth/profile.php' : 'auth/profile.php'; ?>" 
                       class="<?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">👤 Profile</a></li>
                
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'dashboard.php' : 'admin/dashboard.php'; ?>" 
                           class="admin-link <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'active' : ''; ?>">⚙️ Admin</a></li>
                <?php endif; ?>
                
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../auth/logout.php' : 'auth/logout.php'; ?>" 
                       class="logout-btn">🚪 Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../auth/login.php' : 'auth/login.php'; ?>" 
                       class="<?php echo ($current_page === 'login.php') ? 'active' : ''; ?>">🔑 Login</a></li>
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../auth/register.php' : 'auth/register.php'; ?>" 
                       class="<?php echo ($current_page === 'register.php') ? 'active' : ''; ?>">📝 Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script>
// Enhanced Mobile Menu Toggle with Hamburger Animation
function toggleMobileMenu() {
    const navLinks = document.getElementById('navLinks');
    const hamburger = document.getElementById('hamburger');
    
    navLinks.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Close mobile nav when clicking outside
document.addEventListener('click', function(e) {
    const nav = document.querySelector('.nav');
    const navLinks = document.getElementById('navLinks');
    const hamburger = document.getElementById('hamburger');
    
    if (!nav.contains(e.target)) {
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
    }
});

// Close mobile nav when clicking on a link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', function() {
        const navLinks = document.getElementById('navLinks');
        const hamburger = document.getElementById('hamburger');
        
        navLinks.classList.remove('active');
        hamburger.classList.remove('active');
    });
});

// Enhanced scroll effect for header
let lastScrollTop = 0;
const header = document.querySelector('.header');

window.addEventListener('scroll', function() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > lastScrollTop && scrollTop > 100) {
        // Scrolling down
        header.style.transform = 'translateY(-100%)';
    } else {
        // Scrolling up
        header.style.transform = 'translateY(0)';
    }
    
    lastScrollTop = scrollTop;
});
</script>

<style>
/* Enhanced Header Styles */
.header {
    background: var(--gradient-primary);
    color: var(--text-white);
    box-shadow: var(--shadow-lg);
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(20px);
    transition: transform 0.3s ease;
}

.nav {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 2rem;
    height: 70px;
    position: relative;
}

.logo {
    font-size: 1.75rem;
    font-weight: 800;
    text-decoration: none;
    color: var(--text-white);
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.logo:hover {
    transform: scale(1.05);
}

.nav-links {
    display: flex;
    list-style: none;
    gap: 0.5rem;
    align-items: center;
    margin: 0;
    padding: 0;
}

.nav-links a {
    color: var(--text-white);
    text-decoration: none;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

/* Beautiful Animated Underline Effect */
.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 8px;
    left: 50%;
    width: 0;
    height: 2px;
    background: var(--text-white);
    border-radius: 2px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateX(-50%);
}

.nav-links a:hover::after,
.nav-links a.active::after {
    width: calc(100% - 3rem);
}

.nav-links a:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.nav-links a.active {
    background: rgba(255, 255, 255, 0.2);
}

.admin-link {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.logout-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Mobile Menu */
.mobile-menu-btn {
    display: none;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: var(--text-white);
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 12px;
    transition: var(--transition);
    backdrop-filter: blur(10px);
    position: relative;
    width: 50px;
    height: 50px;
}

.mobile-menu-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

/* Hamburger Menu Animation */
.hamburger {
    width: 24px;
    height: 18px;
    position: relative;
    cursor: pointer;
}

.hamburger span {
    display: block;
    position: absolute;
    height: 3px;
    width: 100%;
    background: var(--text-white);
    border-radius: 2px;
    opacity: 1;
    left: 0;
    transform: rotate(0deg);
    transition: 0.25s ease-in-out;
}

.hamburger span:nth-child(1) {
    top: 0px;
}

.hamburger span:nth-child(2) {
    top: 7px;
}

.hamburger span:nth-child(3) {
    top: 14px;
}

.hamburger.active span:nth-child(1) {
    top: 7px;
    transform: rotate(135deg);
}

.hamburger.active span:nth-child(2) {
    opacity: 0;
    left: -60px;
}

.hamburger.active span:nth-child(3) {
    top: 7px;
    transform: rotate(-135deg);
}

@media (max-width: 768px) {
    .nav {
        padding: 0 1rem;
    }
    
    .mobile-menu-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .nav-links {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background: rgba(99, 102, 241, 0.98);
        backdrop-filter: blur(20px);
        flex-direction: column;
        padding: 2rem 1rem;
        gap: 1rem;
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
        box-shadow: var(--shadow-lg);
        z-index: 999;
        height: calc(100vh - 70px);
        overflow-y: auto;
    }
    
    .nav-links.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    .nav-links a {
        font-size: 1.1rem;
        padding: 1rem 2rem;
        width: 100%;
        text-align: center;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 0.5rem;
    }
    
    .nav-links a::after {
        display: none;
    }
}
</style>