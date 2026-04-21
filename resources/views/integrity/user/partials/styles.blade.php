<style>
    .view-pane { animation: fadeIn 0.4s ease-out; }
    .bg-success-soft { background-color: rgba(34, 197, 94, 0.1); }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1); }
    .bg-primary-soft { background-color: rgba(0, 123, 255, 0.1); }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1); }
    .bg-info-soft { background-color: rgba(0, 210, 255, 0.1); }
    .shadow-xs { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .border-top-3 { border-top: 3.5px solid !important; }
    .transition-all { transition: all 0.3s ease; }
    .inventory-filters .form-control, .shop-filters .form-control {
        border-radius: 8px;
        font-size: 0.85rem;
        height: 42px;
        border-color: #eee;
    }
    .inventory-filters .input-group-text, .shop-filters .input-group-text {
        border-radius: 8px 0 0 8px;
        border-color: #eee;
    }
    .inventory-card .data-card, .shop-card .data-card {
        border-radius: 15px;
        background: #fff;
        transition: transform 0.2s;
    }
    .inventory-card .data-card:hover, .shop-card .data-card:hover {
        transform: translateY(-3px);
    }
    .icon-sm { width: 32px; height: 32px; }
    .font-12 { font-size: 12px; }
    .badge-primary-soft { background-color: rgba(0, 123, 255, 0.1); color: #007bff; }
    .refresh-balance-btn {
        opacity: 0.6;
        transition: all 0.3s ease;
        padding: 5px;
        margin-top: -5px;
    }
    .refresh-balance-btn:hover {
        opacity: 1;
        transform: rotate(180deg);
    }
    #backToTop { display: none !important; }

    @media (max-width: 576px) {
        .wallet-sidebar { position: relative; top: 0; }
        .balance-card { padding: 1.5rem; }
        .data-card { padding: 1rem; }
        #backToTop { 
            display: none; /* Controlled by JS scroll but only on mobile */
            bottom: 20px; 
            right: 20px; 
            width: 45px; 
            height: 45px; 
        }
        #backToTop.show-btn { display: block !important; }
    }
</style>
