<?php
// index.php
// 301 Redirect for standard users to login.php while capturing high-value customer loyalty SEO paths
if (!isset($_GET['seo_bot'])) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: https://customer.asbfashion.com/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Advanced Local SEO & Loyalty Portal Optimization -->
    <title>Customer Portal | ASB Fashion - Glamour Gate & Loyal Vault</title>
    <meta name="description" content="Access the official ASB Fashion Customer Portal. Secure login for ASB Glamour Gate VIP members and Loyal Customers. Access your ASB Loyal Vault, check rewards, and find our Google Maps showroom locations in Panadura, Matara, Negombo, Anuradhapura, Balangoda, Tangalle, and islandwide.">
    <meta name="keywords" content="ASB Fashion customer login, ASB Glamour Gate, ASB Loyal Vault, ASB loyalty rewards, ASB fashion portal, Sri Lanka leading clothing retail brand, ASB clothing network, family fashion store Sri Lanka, Ampara, Anuradhapura, Matara, Kalutara, Panadura, Aluthgama, Mathugama, Tangalle, Monaragala, Kuliyapitiya, Warakapola, Balangoda, Negombo, Chilaw, Ambalangoda shopping, VIP fashion rewards Sri Lanka">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://customer.asbfashion.com/">

    <!-- Open Graph / Meta Snippets for Social Discovery -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://customer.asbfashion.com/">
    <meta property="og:title" content="ASB Fashion Customer Portal | Glamour Gate & Loyal Vault Login">
    <meta property="og:description" content="Log in to your ASB Fashion account. The exclusive gateway for our loyal customers and Glamour Gate inner circle across Sri Lanka.">
    <meta property="og:image" content="https://customer.asbfashion.com/assets/images/og-main-banner.jpg">

    <!-- Twitter Card Data -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="ASB Fashion Customer Portal | Glamour Gate Rewards">
    <meta property="twitter:description" content="Secure account access to the ASB Loyal Vault for Sri Lanka's leading clothing retail brand.">

    <!-- Tailwind CSS CDN for pristine crawler structural hierarchy -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Deep Google Maps Location & Loyalty Business Schema (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ClothingStore",
      "name": "ASB Fashion",
      "alternateName": ["ASB Fashions", "ASB Glamour Gate", "ASB Loyal Vault"],
      "description": "ASB Fashion is Sri Lanka's premier clothing retailing brand. This official customer hub provides tier access to the ASB Glamour Gate membership and ASB Loyal Vault rewards across all major national retail locations.",
      "url": "https://customer.asbfashion.com/",
      "logo": "https://customer.asbfashion.com/assets/images/logo.png",
      "priceRange": "$$",
      "telephone": "+94112345678",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Panadura",
        "addressRegion": "Western Province",
        "addressCountry": "LK"
      },
      "hasMap": "https://www.google.com/maps",
      "areaServed": [
        {"@type": "AdministrativeArea", "name": "Ampara"},
        {"@type": "AdministrativeArea", "name": "Anuradhapura"},
        {"@type": "AdministrativeArea", "name": "Matara"},
        {"@type": "AdministrativeArea", "name": "Kalutara"},
        {"@type": "AdministrativeArea", "name": "Panadura"},
        {"@type": "AdministrativeArea", "name": "Aluthgama"},
        {"@type": "AdministrativeArea", "name": "Mathugama"},
        {"@type": "AdministrativeArea", "name": "Tangalle"},
        {"@type": "AdministrativeArea", "name": "Monaragala"},
        {"@type": "AdministrativeArea", "name": "Kuliyapitiya"},
        {"@type": "AdministrativeArea", "name": "Warakapola"},
        {"@type": "AdministrativeArea", "name": "Balangoda"},
        {"@type": "AdministrativeArea", "name": "Negombo"},
        {"@type": "AdministrativeArea", "name": "Chilaw"},
        {"@type": "AdministrativeArea", "name": "Ambalangoda"}
      ]
    }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex flex-col justify-between">

    <!-- Structural Semantic Core (Engineered for Search Index Crawlers) -->
    <main class="flex-grow max-w-5xl mx-auto px-6 py-20 text-center">
        <header class="space-y-4">
            <h1 class="text-4xl md:text-6xl font-black tracking-tight text-white uppercase">
                ASB FASHION
            </h1>
            <p class="text-lg md:text-xl font-bold tracking-widest text-amber-400 uppercase">
                Customer Portal & Loyalty Management Network
            </p>
            <p class="text-md text-slate-400 max-w-xl mx-auto">
                Sri Lanka's Leading Clothing Retailing Brand's Exclusive Central Registry.
            </p>
        </header>

        <!-- Semantic Keyword Density Clusters -->
        <section class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 text-left max-w-4xl mx-auto">
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h3 class="text-lg font-semibold text-white mb-2">ASB Glamour Gate</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Our premier elite access ecosystem. Unlock luxury fashion rewards, early tier seasonal rollouts, and unique updates designed exclusively for high-profile trendsetters.
                </p>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h3 class="text-lg font-semibold text-white mb-2">Loyal Customers</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Designed to give back to the millions who choose ASB Fashions as their primary family clothing store. Seamless point accumulation across our retail grid.
                </p>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h3 class="text-lg font-semibold text-white mb-2">ASB Loyal Vault</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Your secure repository for account validation, collected rewards, and custom portal profiles. Optimized for backend stability and instant database synchronization.
                </p>
            </div>
        </section>

        <div class="mt-12 text-center">
            <p class="text-slate-500 text-sm mb-4">
                Please note: This is the dedicated transaction portal address for authenticated customers and is distinct from our main consumer marketing interface.
            </p>
            <a href="login.php" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-bold rounded-md text-slate-950 bg-amber-400 hover:bg-amber-300 transition duration-150 shadow-lg shadow-amber-500/10">
                Enter Secure Account Login
            </a>
        </div>

        <!-- Geographic Google Maps Anchor Points -->
        <section class="mt-20 border-t border-slate-900 pt-12">
            <h2 class="text-sm font-bold text-slate-400 tracking-wider uppercase mb-8">
                Verified Google Maps Customer Hub Coordinates & Branch Showrooms
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 text-xs text-slate-400">
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Ampara Hub</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Anuradhapura City</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Matara Regional</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Kalutara District</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Panadura Head Office</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Aluthgama Center</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Mathugama Hub</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Tangalle Coastal</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Monaragala Outlet</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Kuliyapitiya Showroom</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Warakapola Town</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Balangoda Valley</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Negombo Commercial</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Chilaw Central</div>
                <div class="p-2 bg-slate-900/50 rounded border border-slate-800/60">Ambalangoda Store</div>
            </div>
        </section>
    </main>

    <!-- Deep Crawl Meta Footer -->
    <footer class="bg-slate-950 text-slate-600 py-6 text-center text-xs border-t border-slate-900/60">
        <p>© <?php echo date('Y'); ?> ASB Fashions Sri Lanka. Glamour Gate Portal Infrastructure.</p>
        <p class="mt-1 text-slate-700">All transactional directories, login components, and Loyal Vault profiles are monitored securely under provincial system protocols.</p>
    </footer>

</body>
</html>