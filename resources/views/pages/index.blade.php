@extends('layout')

@section('content')
    <!--main content start-->
    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h2>scram test</h2>
                    {{--@include('admin.errors')--}}
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha512/0.8.0/sha512.min.js"></script>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsSHA/2.3.1/sha256.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsSHA/2.3.1/sha512.js"></script>

                    <h3>Регистрация</h3>
                    <form action="/scram/register" class="js_register">
                        <input type="hidden" value="register" name="form_type">
                        <input type="text" placeholder="your login" name="user_login" >
                        <input type="text" placeholder="your password" name="user_password">
                        <input type="submit" class="form_submit" value="register">
                    </form>

                    <?/* GetNonce for debugging
<h3>GetNonce</h3>
<form action="/scram/getnonce" class="">
    <input type="hidden" value="auth" name="form_type">
    <input type="text" placeholder="your login" name="user_login" >
    <input type="text" placeholder="your password" name="user_password">
    <input type="hidden" name="client_proof" value="asdfasdf">
    <button type="submit" class="form_submit">GetNonce</button>
</form>
*/?>

                    <h3>Авторизация</h3>
                    <form action="/scram/verifyNonce" class="js_auth">
                        <input type="hidden" value="auth" name="form_type">
                        <input type="text" placeholder="your login" name="user_login" >
                        <input type="text" placeholder="your password" name="user_password">
                        <input type="hidden" name="client_proof" value="asdfasdf">
                        <button type="submit" class="form_submit">Authorize</button>
                    </form>

                    <br>
                    <div id="msg">message will be here</div>
                    <script>
                        function strXor( a, b ) {
                            var len = Math.min( a.length, b.length );
                            var arr = new Array( len );
                            for( var i = 0; i < len; ++i )
                                arr[i] = a.charCodeAt(i) ^ b.charCodeAt(i);
                            return String.fromCharCode.apply( null, arr );
                        }

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
                                            msg.innerHTML = '';
                                            msg.append(data['msg']);
                                            console.log(response);
                                        }
                                    } catch (e) {
                                        console.log('Error: '+ e);
                                        console.log('response: '+ response);
                                    }
                                }
                            });

                        });


                        $(".js_auth").on('submit', function (event) {
                            event.preventDefault();
                            let t = $(this);
                            let action = t.attr('action');
                            let msg = document.getElementById('msg');
                            var server_nonce = 'empty nonce';
                            let form_data = t.serialize();
                            let password = t.find("input[name=user_password]").val();

                            //сначала получим серверный нонс
                            $.ajax({
                                type: "POST",
                                url: '/scram/getnonce',
                                data: form_data,
                                success: function (response) {
                                    try {
                                        let data = JSON.parse(response);
                                        if (data['status'] === true) {
                                            msg.innerHTML = '';
                                            msg.append(data['msg'] + ' nonce='+data['nonce']);

                                            server_nonce = data['nonce'];
                                            let client_proof = strXor(sha256(password), sha256(server_nonce + sha256(sha256(password))));
                                            console.log('password: '+password);
                                            console.log('server_nonce: '+server_nonce);
                                            console.log('client_proof: '+client_proof);
                                            $('<input />').attr('type', 'hidden')
                                                .attr('name', "client_proof")
                                                .attr('value', client_proof)
                                                .appendTo(t);

                                            form_data = t.serialize();//получим свежую form_data с client_proof-ом

                                            check_auth(form_data,server_nonce,msg,action);
                                        } else {
                                            msg.innerHTML = '';
                                            msg.append(data['msg']);
                                            console.log(response);
                                        }
                                    } catch (e) {
                                        console.log('Error: '+ e);
                                        console.log('response: '+ response);
                                    }
                                }
                            });

                            function check_auth(form_data,server_nonce,msg,action){
                                $.ajax({
                                    type: "POST",
                                    url: action,
                                    data: form_data,
                                    success: function (response) {
                                        try {
                                            let data = JSON.parse(response);
                                            if (data['status'] === true) {
                                                //msg.innerHTML = '';
                                                msg.append('\n\n');
                                                msg.append(data['msg']);
                                            } else {
                                                msg.innerHTML = '';
                                                msg.append(data['msg']);
                                                console.log(response);
                                            }
                                        } catch (e) {
                                            console.log('Error: '+ e);
                                            console.log('response: '+ response);
                                        }
                                    }
                                });
                            }

                        });
                    </script>



                    <h3>Тестовая проверка SCRAM авторизации одним кликом:<button id="check_auth">check auth</button></h3>

                    <div id="server_nonce_msg">server nonce will be here</div>
                    <div id="check_auth_msg">auth_msg will be here</div>



                    <script>
                        $("#check_auth").on('click',function(event) {
                            //сначала получим server_nonce путем запроса к серверу
                            let client_id = 'test_login';
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
                                            msg.append(data['msg'] + data['nonce']);
                                            server_nonce = data['nonce'];
                                            check_auth_fast(server_nonce,password);
                                        } else {
                                            console.log(response);
                                        }
                                    } catch (e) {
                                        console.log(response);
                                    }
                                }
                            });
                        });

                        function check_auth_fast(server_nonce,password) {

                            let msg = document.getElementById('check_auth_msg');
                            msg.innerHTML = '';
                            //$client_proof = hash('sha256',$password,true) ^ hash('sha256',$server_nonce.hash('sha256',hash('sha256',$password,true)));
                            let client_proof = strXor(sha256(password), sha256(server_nonce + sha256(sha256(password))));

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
                    </script>








                </div>
                @include('pages._sidebar')
            </div>
        </div>
    </div>
    <!-- end main content-->
@endsection