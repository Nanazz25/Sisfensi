/**
 * Integrity Admin Dashboard Management
 */

$(document).ready(function() {
    // Logic Sync Function for Rule Modals
    function syncModalLogic(modal) {
        const triggerType = modal.find('[name="trigger_type"]').val();
        
        if (triggerType === 'manual') {
            modal.find('.attendance-wrapper').addClass('d-none').find('input, select').prop('disabled', true);
            modal.find('.manual-wrapper').removeClass('d-none').find('input, select').prop('disabled', false);
        } else {
            modal.find('.attendance-wrapper').removeClass('d-none').find('input, select').prop('disabled', false);
            modal.find('.manual-wrapper').addClass('d-none').find('input, select').prop('disabled', true);
            
            // Sync current basis
            const basis = modal.find('.basis-select').val();
            modal.find('.logic-group').addClass('d-none').find('input, select').prop('disabled', true);
            modal.find('.' + basis + '-logic').removeClass('d-none').find('input, select').prop('disabled', false);
        }
    }

    // Handle Trigger & Basis Changes
    $(document).on('change', '#triggerTypeSelect, #edit_rule_trigger, .basis-select', function() {
        syncModalLogic($(this).closest('.modal'));
    });

    // Run Sync whenever any modal is shown to ensure clean state
    $('.modal').on('show.bs.modal', function() {
        const modal = $(this);
        setTimeout(() => syncModalLogic(modal), 100);
    });

    // Handle Edit Rule
    $('.btn-edit-rule').on('click', function() {
        const rule = $(this).data('rule');
        const url = $(this).data('url');
        const modal = $('#editRuleModal');
        
        modal.find('form').attr('action', url);
        modal.find('[name="rule_name"]').val(rule.rule_name);
        
        modal.find('[name="trigger_type"]').val(rule.trigger_type);
        modal.find('[name="basis_type"]').val(rule.basis_type);
        modal.find('[name="point_modifier"]').val(rule.point_modifier);

        if (rule.basis_type === 'status') {
            modal.find('.status-logic [name="condition_value"]').val(rule.condition_value);
        } else if (rule.basis_type === 'setting') {
            modal.find('.setting-logic [name="reference_key"]').val(rule.reference_key);
            modal.find('.setting-logic [name="condition_operator"]').val(rule.condition_operator);
            modal.find('.setting-logic [name="offset_minutes"]').val(rule.offset_minutes);
        } else {
            modal.find('.fixed-logic [name="condition_operator"]').val(rule.condition_operator);
            modal.find('.fixed-logic [name="condition_value"]').val(rule.condition_value);
        }
        
        // Initial sync after data is filled
        syncModalLogic(modal);
        modal.modal('show');
    });

    // Handle Item Type Change (Show/Hide Effect Value)
    $(document).on('change', '.item-type-select', function() {
        const modal = $(this).closest('.modal');
        if ($(this).val() === 'LATE_EXEMPTION') {
            modal.find('.item-effect-group').removeClass('d-none');
        } else {
            modal.find('.item-effect-group').addClass('d-none');
        }
    });

    // Handle Edit Item
    $('.btn-edit-item').on('click', function() {
        const item = $(this).data('item');
        const url = $(this).data('url');
        const modal = $('#editItemModal');
        
        modal.find('form').attr('action', url);
        modal.find('#edit_item_name').val(item.item_name);
        modal.find('#edit_item_type').val(item.item_type).trigger('change');
        modal.find('#edit_item_cost').val(item.point_cost);
        modal.find('#edit_item_limit').val(item.purchase_limit);
        modal.find('#edit_item_period').val(item.purchase_period);
        modal.find('#edit_item_desc').val(item.description);
        modal.find('#edit_item_effect').val(item.effect_value);
        
        modal.modal('show');
    });

    // Handle Delete Modal for Rules & Items
    $('.btn-delete-rule, .btn-delete-item').on('click', function() {
        const url = $(this).data('url');
        const name = $(this).data('name');
        const isRule = $(this).hasClass('btn-delete-rule');
        
        $('#deleteModal .title').text(isRule ? 'Hapus Aturan Integritas' : 'Hapus Item Marketplace');
        $('#deleteForm').attr('action', url);
        $('#deleteItemName').text(name);
        $('#deleteModal').modal('show');
    });
});
