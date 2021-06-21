<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>М@К - Авторизація</title>

<link href="../css/reset.css" rel="stylesheet" type="text/css" />

<!--[if IE]>
    <link href="../css/auth_ie.css" rel="stylesheet" type="text/css" />
<![endif]-->

<![if (IE 7) & (IE 8)]>
    <link href="../css/auth.css" rel="stylesheet" type="text/css" />
<![endif]>

<script type='text/javascript' src='lib/jQuery/jquery-1.3.2.min.js'></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#loginform").show();
    });

    function onLangChange(){
        var lang = document.getElementById("_lang").value;
        document.location.href = "/auth?_=1374051291544&lang=" + lang;
    }
</script>

</head>
<body>
    <div id="container">
        <form name="login" action="auth" method="post">
            <label for="username">Логін:</label>
            <input id="username" name="username" tabindex="1" type="name" autofocus>
            <label for="password">Пароль:</label>
            <input id="password" name="password" tabindex="2" type="password">
            <label for="language">Мова:</label>
            <select id='_lang' class = "language" name="_lang" tabindex="3" type="language" onchange="onLangChange()">
                <option selected value="ua">українська</option>
                <option value="ru">російська</option>
            </select>
            <div id="lower">
                <input id="signin_submit" type="submit" tabindex="4" value="Вхід">
                <a href="/guest"><input class="guest" tabindex="5" value="Вільний доступ" readonly></a>
            </div>
        </form>
    </div>
</body>
</html>