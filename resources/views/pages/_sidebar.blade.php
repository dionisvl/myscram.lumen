<h2>scram test</h2>
{{--@include('admin.errors')--}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha512/0.8.0/sha512.min.js"></script>

<h3>Регистрация</h3>
<form action="/scram/register" class="js_register">
    <input type="hidden" value="register" name="form_type">
    <input type="text" placeholder="your login" name="user_login" >
    <input type="text" placeholder="your password" name="user_password">
    <input type="submit" class="form_submit" value="register">
</form>

<h3>Авторизация</h3>
<form action="/scram/auth" class="js_auth">
    <input type="hidden" value="auth" name="form_type">
    <input type="text" placeholder="your login" name="user_login" >
    <input type="text" placeholder="your password" name="user_password">
    <button type="submit" class="form_submit">Authorize</button>
</form>

<br>
<div id="msg">message will be here</div>
<script>
    $(".js_register").on('submit', function (event) {
        event.preventDefault();
        let t = $(this);
        let action = t.attr('action');
        let msg = document.getElementById('msg');
        let data = t.serialize();

        $.ajax({
            type: "POST",
            url: action,
            data: data,
            success: function (response) {
                try {
                    let data = JSON.parse(response);
                    if (data['status'] === true) {
                        msg.innerHTML = '';
                        msg.append(data['msg']);

                    } else {
                        console.log(response);
                    }
                } catch (e) {
                    console.log(response);
                }
            }
        });

    });


    $(".js_auth").on('submit', function (event) {
        event.preventDefault();
        let t = $(this);
        let action = t.attr('action');
        let client_id = '123';
        let msg = document.getElementById('msg');
        var server_nonce = 'empty nonce';

        //сначала получим серверный нонс
        $.ajax({
            type: "POST",
            url: '/scram/getnonce',
            data: { id: client_id },
            success: function (response) {
                try {
                    let data = JSON.parse(response);
                    if (data['status'] === true) {
                        msg.innerHTML = '';
                        msg.append(data['msg'] + ' nonce='+data['nonce']);
                        server_nonce = data['nonce'];

                        $('<input />').attr('type', 'hidden')
                            .attr('name', "server_nonce")
                            .attr('value', server_nonce)
                            .appendTo(t);



                        let data = t.serialize();
                        check_auth(server_nonce,msg,action);
                    } else {
                        console.log(response);
                    }
                } catch (e) {
                    console.log(response);
                }
            }
        });

        function check_auth(data,msg,action){
            $.ajax({
                type: "POST",
                url: action,
                data: data,
                success: function (response) {
                    try {
                        let data = JSON.parse(response);
                        if (data['status'] === true) {
                            msg.innerHTML = '';
                            msg.append(data['msg']);

                        } else {
                            console.log(response);
                        }
                    } catch (e) {
                        console.log(response);
                    }
                }
            });
        }

    });
</script>



<h3>Тестовая проверка SCRAM авторизации:<button id="check_auth">check auth</button></h3>

<div id="server_nonce_msg">server nonce will be here</div>
<div id="check_auth_msg">auth_msg will be here</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha512/0.8.0/sha512.min.js"></script>

<script>
    $("#check_auth").on('click',function(event) {
        //сначала получим server_nonce путем запроса к серверу
        let client_id = '123';
        let msg = document.getElementById('server_nonce_msg');
        var server_nonce = 'empty nonce';
        let password = 'HELLO_WORLD';

        $.ajax({
            type: "POST",
            url: '/scram/getnonce',
            data: { id: client_id },
            success: function (response) {
                try {
                    let data = JSON.parse(response);
                    if (data['status'] === true) {
                        msg.innerHTML = '';
                        msg.append(data['msg'] + ' nonce='+data['nonce']);
                        server_nonce = data['nonce'];
                        check_auth(server_nonce,password);
                    } else {
                        console.log(response);
                    }
                } catch (e) {
                    console.log(response);
                }
            }
        });
    });

    function check_auth(server_nonce,password) {
        let msg = document.getElementById('check_auth_msg');
        let stored_key = sha256(sha256(password));
        //$client_proof = hash('sha256',$password,true) ^ hash('sha256',$server_nonce.hash('sha256',hash('sha256',$password,true)));
        let client_proof = strXor(sha256(password), sha256(server_nonce + stored_key));

        $.ajax({
            type: "POST",
            url: '/scram/verifyNonce',
            data: { client_proof: client_proof },
            success: function (response) {
                try {
                    let data = JSON.parse(response);
                    if (data['status'] === true) {
                        msg.innerHTML = '';
                        msg.append(data['msg']);

                    } else {
                        console.log(response);
                    }
                } catch (e) {
                    console.log(response);
                }
            }
        });
    }

    function strXor( a, b ) {
        var len = Math.min( a.length, b.length );
        var arr = new Array( len );
        for( var i = 0; i < len; ++i )
            arr[i] = a.charCodeAt(i) ^ b.charCodeAt(i);
        return String.fromCharCode.apply( null, arr );
    }
</script>

<?
//echo hash('sha256','HELLO_WORLD');

//print_r('sendData: '. sendData('mydata').PHP_EOL);
//
//function sendData($data) {
//    $password = 'HELLO_WORLD';
//    $server_nonce = getNonce(1);//получим серверный нонс ;
//    // $stored_key = hash('sha1',hash('sha1',$password));
//
//    $client_proof = hash('sha256',$password,true) ^ hash('sha256',$server_nonce.hash('sha256',hash('sha256',$password,true)));
//
//    return verifyNonce($data, $client_proof);// sendDataToClient();
//}








