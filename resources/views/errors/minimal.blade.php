<html xmlns="http://www.w3.org/1999/xhtml"><head>
            <title>@yield('title')</title>

<script language="javascript" type="text/javascript">
    function windowClose() {
        window.open('','_parent','');
        window.close();
    }
    function goBack() {
    window.history.back();
    }
</script>
<style>

            body {
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            }
        
.pageContent {
  width: 100%;
  height: 100%;
  background-color: #272727;
}
.centerContent {
  background-color: #FD8932;
  position: absolute;
  height: 300px;
  width: 100%;
  top: 35%;
  left: 0%;
}

.textContent {
  color: white;
  position: absolute;
  text-align: center;
  top: 25%;
  left: 0;
  width: 100%;
  font-size: 30px;
  letter-spacing: -1px;
}

input.MyButton {
  width: 200px;
  padding: 20px;
  cursor: pointer;
  font-size: 80%;
  background: #FD8932;
  color: #fff;
  border: 1px solid #272727;
  border-radius: 10px;
  -moz-box-shadow:: 6px 6px 5px #999;
  -webkit-box-shadow:: 6px 6px 5px #999;
  box-shadow:: 6px 6px 5px #999;
}

input.MyButton:hover {
  color: #FD8932;
  background: #272727;
  border: 1px solid #fff;
}
</style></head>


<body class="pageContent">
  <div class="centerContent">
    <div class="textContent">
          @yield('message') <small>@yield('code')</small><br>
        {{ $exception->getMessage() ?? 'Whoops.. error' }}<br>
      <br>
      <input class="MyButton" type="button" value="Go back" onclick="goBack();windowClose();">
      <script>
      </script>
    </div>
  </div>

</body></html>
