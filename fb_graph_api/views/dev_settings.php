<div class="box mb">
    <h1><?=lang('dev_settings');?></h1>
    <?=form_open($save_dev_settings, 'class="settings"', $form_hidden);?>
    <?=ee('CP/Alert')->get('dev_saved');?>

    <fieldset class="col-group">
        <div class="setting-txt col w-8">
            <h3><?=lang('show_error_msg');?></h3>
            <em><?=lang('show_error_msg_desc');?></em>
        </div>
        <div class="setting-field col w-8 last">
            <?=form_checkbox('show_error_msg', '1', ($show_error_msg === 1 ? TRUE : FALSE ));?>
        </div>
    </fieldset>

    <fieldset class="col-group">
        <div class="setting-txt col w-8">
            <h3><?=lang('pretty_print_json');?></h3>
            <em><?=lang('pretty_print_desc');?></em>
        </div>
        <div class="setting-field col w-8 last">
            <?=form_checkbox('pretty_print_json', '1', ($pretty_print_json === 1 ? TRUE : FALSE ));?>
        </div>
    </fieldset>

    <fieldset class="col-group">
        <div class="setting-txt col w-8">
            <h3><?=lang('show_metadata');?></h3>
            <em><?=lang('show_metadata_desc');?></em>
        </div>
        <div class="setting-field col w-8 last">
            <?=form_checkbox('show_metadata', '1', ($show_metadata === 1 ? TRUE : FALSE ));?>
        </div>
    </fieldset>

    <fieldset class="form-ctrls">
        <?=form_submit('submit', lang('save_settings'), 'class="btn"')?>
    </fieldset>

    <?=form_close()?>
</div>
