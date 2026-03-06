<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="title" id="confirmModalTitle">Konfirmasi</h4>
            </div>

            <div class="modal-body text-center">
                <p id="confirmModalMessage"></p>
                <strong id="confirmModalItemName"></strong>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Batal
                </button>

                <form id="confirmForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" id="confirmModalSubmitBtn" class="btn">
                        <i class="fa fa-check mr-1" id="confirmModalIcon"></i> <span
                            id="confirmModalSubmitText">Yakin</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>