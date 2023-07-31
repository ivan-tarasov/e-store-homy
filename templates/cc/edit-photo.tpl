<script src="/js/jquery-1.11.2.min.js"></script>
<script src="/js/jquery.form.min.js"></script>
<link rel="stylesheet" href="/bower/cc/css/jquery.fileupload.css">
<script>
$(document).on('change', '.btn-file :file', function() {
  var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
      input.trigger('fileselect', [numFiles, label]);
});

$(document).ready( function() {
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        
        var input = $(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' фото выбрано' : label;
        
        if( input.length ) {
            input.val(log);
        } else {
            if( log ) alert(log);
        }
        
    });
});
</script>

<div class="row">
   <div class="form-group col-md-12 col-sm-12 no-margin hover">
      {back_link}
   </div>
</div>

<div class="row">
   <div class="form-group col-md-12 col-sm-12 no-margin hover">
      {item_photos}
   </div>
</div>

<div class="row">
   <form id="photo-form" name="photo-form" action="/class/ajax/photoupload.ajax.php" method="post" enctype="multipart/form-data">
      <div class="form-group col-md-10 col-sm-12 no-margin hover">
         <span id="response"></span>
         <div class="input-group">
            <span class="input-group-btn">
               <span class="btn btn-primary btn-file">
                  <i class="fa fa-photo fa-lg"></i>
                  Добавить фотографии&hellip; <input type="file" id="photo-file" name="photo-file[]" multiple />
               </span>
            </span>
            <input type="text" class="form-control" readonly>
         </div>
         <span class="help-block">
            
         </span>
      </div>
      <div class="col-md-2 col-sm-12">
         <button type="submit" id="csv-submit" class="btn btn-default">Загрузить фото</button>
      </div>
   </form>
</div>
<div class="progress">
   <div id='bar' class="progress-bar bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="">
      <span id="percent" class="percent"></span>
   </div>
</div>
<div id="status"></div>

<script>
(function() {
    
var bar = $('.bar');
var percent = $('.percent');
var status = $('#status');
   
$('form').ajaxForm({
    beforeSend: function() {
        status.empty();
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
		//console.log(percentVal, position, total);
    },
    success: function() {
        var percentVal = '100%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
	complete: function(xhr) {
		status.html(xhr.responseText);
	}
}); 

})();
</script>