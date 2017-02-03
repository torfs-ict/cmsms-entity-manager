<pre>
    {$iteration|var_dump}
</pre>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h3>{$entity_obj->Name()|htmlentities}</h3>
        </div>
    </div>
    <div class="row">
        {if $iteration is even}
            <article role="main" class="col-xs-12 col-sm-9 col-md-8">
                {$entity_obj->text}
            </article>
            <figure class="col-xs-12 col-sm-3 col-md-4 thumbnail">
                <img src="{EntityPropertyThumbnail entity=$entity_obj->Id() property="img"}" alt="">
            </figure>
        {else}
            <figure class="col-xs-12 col-sm-3 col-md-4 thumbnail">
                <img src="{EntityPropertyThumbnail entity=$entity_obj->Id() property="img"}" alt="">
            </figure>
            <article role="main" class="col-xs-12 col-sm-9 col-md-8">
                {$entity_obj->text}
            </article>
        {/if}
    </div>
</div>