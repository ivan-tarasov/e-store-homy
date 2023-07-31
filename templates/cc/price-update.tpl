<link rel="stylesheet" href="/bower/cc/css/jquery.fileupload.css">
<style>.fix-scroll {height:224px; overflow:auto; background:#fff;}</style>

<script>
$(document).on('change', '.btn-file :file', function() {
  var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
  input.trigger('fileselect', [numFiles, label]);
});

$(document).ready( function() {
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {

        var input = $(this).parents('.input-group').find(':text');

        input.val(label);

    });
});

$(document).ready(function(){
   $( '#csv-price-form' ).submit(function() {

      var formData = new FormData($(this)[0]);

      $.ajax({
         url: '/class/ajax/csvupload.ajax.php',
         type: 'POST',
         data: formData,
         async: true,
         cache: false,
         contentType: false,
         processData: false,
         statusCode: {
            404: function() {
               $( '#results' ).html("Файл не выбран!");
            }
         }
      }).done(function(returndata) {
         $( '#csv-submit' ).prop('disabled', true);
         $( '#csv-submit' ).html('Обновляю <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>');
         $( '#progressor' ).removeClass( "progress-bar-success" );

         $( '#results' ).html(returndata + "<br />");
         es = new EventSource('/class/ajax/csvtomysql.ajax.php');
         //a message is received
         es.addEventListener('message', function(e) {
             var result = JSON.parse( e.data );

             if (result.message != null)
               addLog(result.message);

             if(e.lastEventId == 'CLOSE') {
                 //addLog('Received CLOSE closing');
                 es.close();
                 var pBar = document.getElementById('progressor');
                 pBar.value = pBar.max; //max out the progress bar

                 $( '#progressor' ).addClass( "progress-bar-success" );
                 $( '#csv-submit' ).removeAttr( "disabled" );
                 $( '#csv-submit' ).html('Обновить еще раз');
             }
             else {
                 var pBar = document.getElementById('progressor');
                 pBar.value = result.progress;
                 var perc = document.getElementById('percentage');
                 perc.innerHTML   = result.progress  + "%";
                 //perc.style.width = (Math.floor(pBar.clientWidth * (result.progress/100)) + 15) + 'px';
                 pBar.style.width = result.progress  + "%";
             }

         });

         es.addEventListener('error', function(e) {
             addLog('Error occurred');
             es.close();
         });
      });

      function addLog(message) {
          var r = document.getElementById('results');
          r.innerHTML += message + '<br />';
          r.scrollTop = r.scrollHeight;
      }

      return false;

   });
});
</script>

<div class="row">
   <form id="csv-price-form" name="csv-price-form">
      <div class="form-group col-md-10 col-sm-12 no-margin hover">
         <div class="input-group">
            <span class="input-group-btn">
               <span class="btn btn-primary btn-file">
                  Выбрать прайс-лист&hellip; <input type="file" id="csv-file" name="csv-file" />
               </span>
            </span>
            <input type="text" class="form-control" readonly>
         </div>
         <span class="help-block">
            Прайс-лист в <b>CSV</b> формате.<br />
            Последний раз прайс обновлялся <code>{last_price_update}</code>
         </span>
      </div>
      <div class="col-md-2 col-sm-12">
         <button type="submit" id="csv-submit" class="btn btn-default">Начать обновление</button>
      </div>
   </form>
</div>

<div class="progress">
   <div id='progressor' class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="">
      <span id="percentage"></span>
   </div>
</div>

<div class="panel panel-default">
   <div class="panel-heading">
      <h4>
         <span class="pull-right"><small>v.</small> {uploader_version}</span>
         Вывод обработчика прайс-листа
      </h4>
   </div>
   <div id="results" class="panel-body fix-scroll"></div>
   <div class="panel-footer">

   </div>
</div>
