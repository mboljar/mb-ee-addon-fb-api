<div class="box mb">
    <h1><?=lang('app_settings');?></h1>
    <?=form_open($add_app, 'class="settings"', $form_hidden);?>
    <?=ee('CP/Alert')->get('app_saved');?>

    <fieldset>
        <div class="setting-txt col w-16">
            <h3><?=lang('instructions');?></h3>
            <p><?=lang('instruct');?></p>
        </div>
    </fieldset>

    <fieldset class="col-group">
        <div class="setting-txt col w-6">
            <h3><?=lang('app_id');?></h3>
            <em><?=lang('app_id_desc');?></em>
        </div>
        <div class="setting-field col w-6 last">
            <?=form_input('app_id', $app_id);?>
        </div>
    </fieldset>

    <fieldset class="col-group">
        <div class="setting-txt col w-6">
            <h3><?=lang('app_secret');?></h3>
            <em><?=lang('app_secret_desc');?></em>
        </div>
        <div class="setting-field col w-6 last">
            <?=form_input('app_secret', $app_secret);?>
        </div>
    </fieldset>

<fieldset class="col-group">
    <div class="setting-txt col w-6">
        <h3><?=lang('app_graph_ver_min');?></h3>
        <em><?=lang('app_graph_ver_min_max_desc');?></em>
    </div>
    <div class="setting-field col w-6 last">
        <?=form_input('app_graph_ver_min', $app_graph_ver_min);?>
    </div>
</fieldset>

<fieldset class="col-group">
    <div class="setting-txt col w-6">
        <h3><?=lang('app_graph_ver_max');?></h3>
        <em><?=lang('app_graph_ver_min_max_desc');?></em>
    </div>
    <div class="setting-field col w-6 last">
        <?=form_input('app_graph_ver_max', $app_graph_ver_max);?>
    </div>
</fieldset>

    <fieldset class="col-group">
        <div class="setting-txt col w-6">
            <h3><?=lang('app_graph_ver');?></h3>
            <em><?=lang('app_graph_ver_desc');?></em>
        </div>
        <div class="setting-field col w-6 last">
            <?php $versions = array(); for ($i = $app_graph_ver_min; $i <= $app_graph_ver_max; $i++) { $versions += ['v'.$i.'.0' => 'v'.$i.'.0']; }?>
            <?=form_dropdown('app_graph_ver', $versions, $app_graph_ver);?>
        </div>
    </fieldset>

    <fieldset class="form-ctrls">
        <?=form_submit('submit', lang('save_settings'), 'class="btn"')?>
    </fieldset>

    <?=form_close()?>
</div>

<div class="box snap table-list-wrap">
    <div class="tbl-ctrls">
        <?= form_open($add_token, '', $form_hidden); ?>
        <h3><?= lang('access_tokens'); ?></h3>
        <?=ee('CP/Alert')->get('token_status');?>
        <div class="tbl-wrap">
            <table cellspacing="0">
                <thead>
                <tr>
                    <th><?= lang('selected'); ?></th>
                    <th><?= lang('name'); ?></th>
                    <th><?= lang('token'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($tokens) && !empty($app_id) && !empty($app_secret)) {
                    ?>
                <tr class="no-results">
                    <td colspan="3">
                        <a href="<?= $get_token; ?>" class="btn tn action" id="fb-authorize" style="display:none;"><?= lang('get_access_tokens'); ?></a>
                        <p id="fb-error"><?= lang('token_failure'); ?></p>
                    </td>
                </tr>
                <?php } elseif (!empty($tokens)) {
                    ?>
                    <?php foreach ($tokens as $token) {
                        $radio_status = ($default_token === $token['token']) ? TRUE : FALSE;
                        ?>
                        <tr<?php if ($radio_status) {?> class="selected"<?php } ?>>
                            <td><?= form_radio('default_token', $token['token'], $radio_status); ?></td>
                            <td><?=$token['name'];?></td>
                            <td><?=$token['token'];?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <fieldset class="tbl-bulk-act">
                <?= form_submit('submit', lang('save_tokens'), 'class="btn submit"'); ?>
                <a href="<?= $clear_token; ?>" class="btn action"><?= lang('clear_tokens'); ?></a>
        </fieldset>
        <?= form_close(); ?>
    </div>
</div>

<?php
// Some JS to load the FB login and retrieve an access token
ee()->cp->load_package_js('fb_graph_api');
?>
