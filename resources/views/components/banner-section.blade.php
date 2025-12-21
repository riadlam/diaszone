<div class="page-section banner-section">
    <div class="banners has-grid grid-4">
        <a href="{{ route('freefire') }}" class="banners__item" title="Free Fire Diamonds">
            <img src="{{ url('storage_public/images_homepage/free-fire-1.webp') }}" alt="Free Fire Diamonds">
        </a>
        <a href="{{ route('pubgmobile') }}" class="banners__item" title="PUBG Mobile UC">
            <img src="{{ url('storage_public/images_homepage/pubg-mobile-uc-1.webp') }}" alt="PUBG Mobile UC">
        </a>
        <a href="{{ url('/bigo_live') }}" class="banners__item" title="Bigo Live Gift Card">
            <img src="{{ url('storage_public/images_homepage/bige-live-diamonds-1.webp') }}" alt="Bigo Live Gift Card">
        </a>
        <a href="{{ url('/clash_royale') }}" class="banners__item" title="Clash Royale Gems">
            <img src="{{ url('storage_public/images_homepage/clash-royale-1.webp') }}" alt="Clash Royale Gems">
        </a>
    </div>
</div>

<style>
    .page-section.banner-section {
        padding: 2rem 0;
        background: #ffffff;
    }
    
    .banners.has-grid {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
        width: 100%;
    }
    
    .banners.grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        width: 100%;
    }
    
    .banners__item {
        display: block;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 200px;
        width: 100%;
    }
    
    .banners__item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    
    .banners__item img {
        width: 100%;
        height: 100%;
        display: block;
        border-radius: 12px;
        object-fit: contain;
        object-position: center;
        background-color: #f3f4f6;
    }
    
    @media (max-width: 1024px) {
        .banners.grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .banners__item {
            height: 180px;
        }
    }
    
    @media (max-width: 768px) {
        .banners.has-grid {
            padding: 0 0.75rem;
        }
        
        .banners.grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        
        .page-section.banner-section {
            padding: 1.5rem 0;
        }
        
        .banners__item {
            height: 140px;
        }
    }
    
    @media (max-width: 480px) {
        .banners.has-grid {
            padding: 0 0.5rem;
        }
        
        .banners.grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        
        .banners__item {
            height: 120px;
        }
    }
</style>

