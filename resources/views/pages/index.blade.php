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
{{--                        <input type="hidden" name="client_proof" value="asdfasdf">--}}
                        <button type="submit" class="form_submit">Authorize</button>
                    </form>

                    <br>
                    <div id="msg">message will be here</div>
                    <script>
                        "use strict";

                        /*\
                        |*|  Base64 / binary data / UTF-8 strings utilities (#3)
                        |*|
                        |*|  https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
                        \*/

                        function btoaUTF16 (sString) {
                            var aUTF16CodeUnits = new Uint16Array(sString.length);
                            Array.prototype.forEach.call(aUTF16CodeUnits, function (el, idx, arr) { arr[idx] = sString.charCodeAt(idx); });
                            return btoa(String.fromCharCode.apply(null, new Uint8Array(aUTF16CodeUnits.buffer)));
                        }
                        function atobUTF16 (sBase64) {
                            var sBinaryString = atob(sBase64), aBinaryView = new Uint8Array(sBinaryString.length);
                            Array.prototype.forEach.call(aBinaryView, function (el, idx, arr) { arr[idx] = sBinaryString.charCodeAt(idx); });
                            return String.fromCharCode.apply(null, new Uint16Array(aBinaryView.buffer));
                        }

                        function strXor( a, b ) {
                            let len = Math.min( a.length, b.length );
                            let arr = new Array( len );
                            for( var i = 0; i < len; ++i )
                                arr[i] = a.charCodeAt(i) ^ b.charCodeAt(i);
                            return String.fromCharCode.apply( null, arr );
                        }
                        function sha( data ) {
                            // let favoriteAlgo = 'sha512';
                            // return eval(favoriteAlgo)(data);

                            let favoriteAlgo = 'SHA-512';
                            let shaObj = new jsSHA(favoriteAlgo,'TEXT');
                            shaObj.update(data);
                            return shaObj.getHash("HEX");
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
                                            let client_proof = strXor(sha(password), sha(server_nonce + sha(sha(password))));

                                            console.log('right_part: '+sha(server_nonce + sha(sha(password))));
                                            console.log('client_proof: '+client_proof);
                                            console.log('client_proof B64: '+btoa(client_proof));
                                            console.log('server_nonce: '+server_nonce);
                                            console.log('sha(password): '+sha(password));
                                            console.log('sha(sha(password)): '+sha(sha(password)));

                                            // $('<input />').attr('type', 'hidden')
                                            //     .attr('name', "client_proof")
                                            //     .attr('value', client_proof)
                                            //     .appendTo(t);

                                            form_data = form_data+'&client_proof='+btoa(client_proof);//получим свежую form_data с client_proof-ом

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
                            let user_login = 'test_login';
                            let msg = document.getElementById('server_nonce_msg');
                            var server_nonce = 'empty nonce';
                            let password = 'HELLO_WORLD';

                            $.ajax({
                                type: "POST",
                                url: '/scram/getnonce',
                                data: { user_login: user_login },
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
                            let client_proof = strXor(sha(password), sha(server_nonce + sha(sha(password))));

                            console.log('right_part: '+sha(server_nonce + sha(sha(password))));
                            console.log('client_proof: '+client_proof);
                            console.log('client_proof B64: '+btoa(client_proof));
                            console.log('server_nonce: '+server_nonce);
                            console.log('sha(password): '+sha(password));
                            console.log('sha(sha(password)): '+sha(sha(password)));
                            $.ajax({
                                type: "POST",
                                url: '/scram/verifyNonce',
                                data: { client_proof: btoa(client_proof) },
                                success: function (response) {
                                    try {
                                        let data = JSON.parse(response);
                                        if (data['status'] === true) {
                                            msg.innerHTML = '';
                                            msg.append(data['msg']);

                                        } else {
                                            msg.append(data['msg']);
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