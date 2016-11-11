<div data-buttonset="1">
    <input type="radio" id="{$config->name}_yes" value="1" name="{$config->name}" {if $value === true || ($config->default === true && $value !== false)}checked="checked"{/if}><label for="{$config->name}_yes">Ja</label>
    <input type="radio" id="{$config->name}_no" value="0" name="{$config->name}" {if $value === false || ($config->default === false && $value !== true)}checked="checked"{/if}><label for="{$config->name}_no">Neen</label>
</div>