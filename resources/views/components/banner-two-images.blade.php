<div class="page-section banner-section">
    <div class="banners has-grid grid-2">
        <a href="{{ route('bloodstrike') }}" class="banners__item" title="Blood Strike">
            <img src="{{ url('storage/images_homepage/blood-srike-item4gamer-1-1536x421.webp') }}" alt="Blood Strike" class="w-full h-full object-cover">
        </a>
        <a href="{{ url('/steam_gift_card') }}" class="banners__item" title="Steam Gift Cards">
            <img src="{{ url('storage/images_homepage/steam-gift-card-item4gamer-1-1536x421.webp') }}" alt="Steam Gift Cards" class="w-full h-full object-cover">
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
    }
    
    .banners.grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .banners__item {
        display: block;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 200px;
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
        object-fit: cover;
    }
    
    @media (max-width: 768px) {
        .banners.grid-2 {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .page-section.banner-section {
            padding: 1.5rem 0;
        }
        
        .banners__item {
            height: 150px;
        }
    }
</style>

