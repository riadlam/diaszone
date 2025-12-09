<div id="whatsapp-fab" aria-hidden="false">
    <style>
        /* WhatsApp FAB - site-wide (moved slightly higher) */
        #whatsapp-fab { position: fixed; right: 35px; bottom: 45px; z-index: 9999; }
        .whatsapp-fab-button { display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 999px; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); box-shadow: 0 8px 24px rgba(18,140,126,0.2); cursor: pointer; border: none; }
        .whatsapp-fab-button:focus { outline: 2px solid rgba(255,255,255,0.28); outline-offset: 2px; }
        .whatsapp-fab-icon { width: 24px; height: 24px; color: #fff; }
        .whatsapp-fab-label { margin-left: 10px; background: #0b9449; color: #fff; padding: 6px 12px; border-radius: 999px; font-weight: 600; display: none; align-items: center; gap:8px; }
        @media (min-width: 768px) {
            .whatsapp-fab-label { display: inline-flex; }
        }
        /* subtle floating animation */
        .whatsapp-fab-button { animation: wobble 6s infinite; }
        @keyframes wobble { 0%,100%{ transform: translateY(0);} 50%{ transform: translateY(-3px);} }
    </style>

    <a id="whatsapp-fab-link" href="https://wa.me/213556988175" target="_blank" rel="noopener noreferrer" aria-label="Contact us on WhatsApp +213556988175" class="whatsapp-fab-link" onclick="window.dataLayer && window.dataLayer.push({event:'whatsapp_click'});">
        <button class="whatsapp-fab-button" type="button" id="whatsapp-fab-btn" aria-pressed="false">
            <svg class="whatsapp-fab-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.92 11.92 0 0012.02.02h-.01C5.43.02.25 5.2.25 11.79c0 2.08.55 4.12 1.6 5.92L.05 23.95l6.4-1.7c1.7 1 3.67 1.52 5.7 1.52h.01c6.59 0 11.78-5.18 11.78-11.78 0-3.15-1.23-6.09-3.42-8.01zM12.02 20.87c-1.85 0-3.66-.5-5.23-1.44l-.37-.22-3.9 1.03 1.04-3.82-.24-.39c-1.03-1.69-1.6-3.61-1.6-5.58 0-5.03 4.09-9.12 9.12-9.12 2.44 0 4.72.95 6.44 2.67 1.72 1.72 2.67 4 2.67 6.44 0 5.03-4.09 9.12-9.12 9.12z"/></svg>
        </button>
    </a>

    <script>
        // Accessibility: keyboard open of WhatsApp link
        document.getElementById('whatsapp-fab-btn')?.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                document.getElementById('whatsapp-fab-link').click();
            }
        });
    </script>
</div>
