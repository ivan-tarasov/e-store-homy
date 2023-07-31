<script type="text/javascript">
var count = {redirect_timer};
var redirect = "{redirect_url}";
 
function countDown(){
    var timer = document.getElementById("timer");
    if(count > 0){
        count--;
        timer.innerHTML = "Вы будите перенаправлены через "+count+" сек.";
        setTimeout("countDown()", 1000);
    }else{
        window.location.href = redirect;
    }
}
</script>

<span id="timer">
   <script type="text/javascript">countDown();</script>
</span>