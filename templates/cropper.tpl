<div class="entity-cropper" data-ratio="{$entity->aspectRatio|replace:',':'.'}">
    <div class="dropzone">
        <div class="image dz-message">
            <p>Klik of sleep afbeelding naar hier om te wijzigen...</p>
            <p class="cropper-spinner">{admin_icon icon="icons/extra/spinner.gif" class="cropper-spinner"}</p>
        </div>
    </div>
    <img src="{EntityImage entity=$entity->Id()}" alt="">
    <input type="hidden" name="filename" value="{$entity->filename|htmlentities}">
    <input type="hidden" name="blob" value="">
    <input type="hidden" name="x" value="{$entity->cropX|replace:',':'.'}}">
    <input type="hidden" name="y" value="{$entity->cropY|replace:',':'.'}}">
    <input type="hidden" name="width" value="{$entity->cropWidth|replace:',':'.'}}">
    <input type="hidden" name="height" value="{$entity->cropHeight|replace:',':'.'}}">
</div>