{if $config->date}
    <input
        class="entity"
        type="text"
        data-date="1"
        {if $config->required}required="required"{/if}
    >
    <input
        class="entity date-alt"
        type="text"
        name="{$config->name|htmlentities}"
        value="{$value|default:$config->default}"
        {if $config->required}required="required"{/if}
    >
{else}
    <input
        class="entity"
        type="{if $config->spinner}text{else}number{/if}"
        name="{$config->name|htmlentities}"
        value="{$value|default:$config->default}"
        {if $config->spinner}data-spinner="1"{/if}
        {if $config->date}data-date="1"{/if}
        {if $config->required}required="required"{/if}
    >
{/if}