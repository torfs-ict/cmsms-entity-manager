{extends file="netdesign_client_tpl:base.tpl"}
{assign var="stellar" value=0}
{block name="content"}
    <div class="container">
        <h2>{$content_obj->Name()|htmlentities}</h2>
        <article role="main">
            {$content_obj->text}
        </article>
    </div>
    {foreach $content_obj->GetChildren() as $child}
        {assign var="iteration" value=$child@iteration}
        {if $child@index is even}
            {assign var="stellar" value=$stellar + 1}
            <section data-stellar-background-ratio="0.5" class="stellar" style="background-image: url({EntityPropertyThumbnail entity=$content_obj->Id() property="stellar" index=$stellar});">
                {$child->Render()}
            </section>
        {else}
            {$child->Render()}
        {/if}
    {/foreach}
{/block}