<script>
$( window ).load(function() {
   swal({
      title: '{title}',
      text: '{message}',
      type: "{type}",
      html: true,
      confirmButtonText: 'Закрыть',
      closeOnConfirm: false
   }, function(){
         window.location.href = "{redirect}";
   });
});
</script>