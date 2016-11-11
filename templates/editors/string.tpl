{function image template=false}
	{EntityPropertyImage entity=$entity->Id() property=$config->name index=$index}
	{if $template eq true}{assign var="tplIndex" value="__index__"}{else}{assign var="tplIndex" value=$index}{/if}
	<div class="container" data-ratio="{$entity->GetAspectRatio($config->name)|replace:',':'.'}" data-url="{EntityAjaxUrl method="CropImage" index=$tplIndex}">
		<input type="hidden" name="x" value="{$image.x|replace:',':'.'}">
		<input type="hidden" name="y" value="{$image.y|replace:',':'.'}">
		<input type="hidden" name="width" value="{$image.width|replace:',':'.'}">
		<input type="hidden" name="height" value="{$image.height|replace:',':'.'}">
		<div class="dropzone" data-ratio="{$config->aspectRatio}" data-url="{EntityAjaxUrl method="UploadImage" index=$tplIndex}">
			<div class="image dz-message">
				<img data-height="100" src="{$image.url}" alt="">
				<p>Klik of sleep afbeelding naar hier om te wijzigen...</p>
				<p class="cropper-spinner">{admin_icon icon="icons/extra/spinner.gif" class="cropper-spinner"}</p>
			</div>
		</div>
		<p class="croplink"><button type="button">Bijsnijden</button></p>
		<div class="entity-property-cropper">
			<img src="">
		</div>
	</div>
{/function}
{if $config->textarea}
    {cms_textarea name=$config->name required=$config->required enablewysiwyg=$config->wysiwyg value=$value|default:$config->default}
{elseif $config->images !== null}
	{if $config->images eq 0}
		<p><a href="javascript:void(0)" id="addImage">Afbeelding toevoegen</a></p>
		<div id="addImageTemplate" style="display: none;">
			{image template=true}
		</div>
	{/if}
    <div class="entity-images">
        {if $entity->Id() < 1}
            <p>U dient de inhoud op te slaan vooraleer u afbeeldingen kan beheren.</p>
        {else}
	        {capture assign="count"}{if $config->images > 0}{$config->images}{else}{$entity->CountImages($config->name)}{/if}{/capture}
            {for $index = 1 to $count}
	            {image}
            {/for}
        {/if}
    </div>
{elseif $config->files !== null}
	<div class="entity-files">
		{if $entity->Id() < 1}
			<p>U dient de inhoud op te slaan vooraleer u bestanden kan beheren.</p>
		{else}
			{for $index = 1 to $config->files}
				{EntityPropertyFile entity=$entity->Id() property=$config->name index=$index}
				<div class="container">
					<div class="dropzone" data-url="{EntityAjaxUrl method="UploadFile" index=$index}">
						<div class="image dz-message">
							<p>
								Klik of sleep bestand naar hier om te wijzigen...
								<span class="dz-progress"><span></span>%</span>
							</p>
						</div>
						<p class="dz-download"{if empty($file.filename)} style="display: none" {/if}><a title="Bestand downloaden" target="_blank" href="{$file.url}">{$file.filename|htmlentities}</a></p>
					</div>
				</div>
			{/for}
		{/if}
	</div>
{else}
    <input
        class="entity"
        type="text"
        name="{$config->name|htmlentities}"
        value="{$value|default:$config->default|htmlentities}"
        {if $config->required}required="required"{/if}
        {if $config->autocomplete}data-autocomplete="{EntityAjaxUrl method="AutoComplete"}"{/if}
        {if $config->tags}data-tags="{EntityAjaxUrl method="Tags"}"{/if}
    >
{/if}