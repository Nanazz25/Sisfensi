<script>
    // Handle persistent view on page reload (for history filters)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('type') || urlParams.has('month') || urlParams.has('sort') || urlParams.has('q')) {
            // Keep history active if it was filtered
            switchView('history', document.querySelector('button[onclick*="history"]'));
        }
    });

    function switchView(viewName, btn) {
        // Reset scroll when switching view
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        document.querySelectorAll('.view-pane').forEach(el => el.classList.add('d-none'));
        const activeView = document.getElementById('view-' + viewName);
        activeView.classList.remove('d-none');
        document.querySelectorAll('.action-btn').forEach(el => el.classList.remove('active'));
        if(btn) btn.classList.add('active');
    }

    // Shop Filter Logic
    function filterShop() {
        const query = document.getElementById('shopSearch').value.toLowerCase();
        const type = document.getElementById('shopTypeFilter').value;
        
        let visibleCount = 0;
        document.querySelectorAll('.shop-card').forEach(card => {
            const matchesSearch = card.getAttribute('data-name').includes(query);
            const matchesType = type === '' || card.getAttribute('data-type') === type;
            
            if (matchesSearch && matchesType) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        const grid = document.getElementById('marketGrid');
        const emptyMsg = document.getElementById('shopEmptyMsg');
        if (visibleCount === 0) {
            if (!emptyMsg) {
                const msg = document.createElement('div');
                msg.id = 'shopEmptyMsg';
                msg.className = 'col-12 text-center py-5 bg-light rounded-lg border border-dashed text-muted';
                msg.innerHTML = '<i class="fa fa-search mb-2 d-block h4"></i> Item tidak ditemukan.';
                grid.appendChild(msg);
            }
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    }

    if(document.getElementById('shopSearch')) {
        document.getElementById('shopSearch').addEventListener('input', filterShop);
        document.getElementById('shopTypeFilter').addEventListener('change', filterShop);
    }

    function resetShop() {
        document.getElementById('shopSearch').value = '';
        document.getElementById('shopTypeFilter').value = '';
        filterShop();
    }

    // Inventory Filter Logic
    function filterInventory() {
        const query = document.getElementById('inventorySearch').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const type = document.getElementById('typeFilter').value;
        
        let visibleCount = 0;
        document.querySelectorAll('.inventory-card').forEach(card => {
            const matchesSearch = card.getAttribute('data-name').includes(query);
            const matchesStatus = status === '' || card.getAttribute('data-status') === status;
            const matchesType = type === '' || card.getAttribute('data-type') === type;
            
            if (matchesSearch && matchesStatus && matchesType) {
                card.classList.remove('d-none');
                visibleCount++;
            } else {
                card.classList.add('d-none');
            }
        });

        const emptyMsg = document.getElementById('inventoryEmptyMsg');
        if (visibleCount === 0) {
            if (!emptyMsg) {
                const msg = document.createElement('div');
                msg.id = 'inventoryEmptyMsg';
                msg.className = 'col-12 text-center py-5 bg-light rounded-lg border border-dashed text-muted';
                msg.innerHTML = '<i class="fa fa-search mb-2 d-block h4"></i> Item tidak ditemukan dengan filter tersebut.';
                document.getElementById('inventoryGrid').appendChild(msg);
            }
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    }

    if(document.getElementById('inventorySearch')) {
        document.getElementById('inventorySearch').addEventListener('input', filterInventory);
        document.getElementById('statusFilter').addEventListener('change', filterInventory);
        document.getElementById('typeFilter').addEventListener('change', filterInventory);
    }

    function resetInventory() {
        document.getElementById('inventorySearch').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        filterInventory();
    }

    // Scroll to Top Logic
    const backToTopBtn = document.getElementById('backToTop');
    window.onscroll = function() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            backToTopBtn.classList.add('show-btn');
        } else {
            backToTopBtn.classList.remove('show-btn');
        }
    };

    if(backToTopBtn) {
        backToTopBtn.onclick = function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
    }

    // Handle Konfirmasi Pembelian
    $(document).on('click', '.buy-item-btn', function() {
        const name = $(this).data('name');
        const cost = $(this).data('cost');
        const url = $(this).data('url');
        
        $('#confirmItemName').text(name);
        $('#confirmItemCost').text(cost);
        $('#confirmBuyForm').attr('action', url);
    });
</script>
