<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="title">{{ $title ?? 'Hapus Data' }}</h4>
            </div>

            <div class="modal-body text-center">
                <p>{{ $message ?? 'Yakin ingin menghapus data ini?' }}</p>
                <strong id="deleteItemName"></strong>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Batal
                </button>

                <form id="deleteForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>