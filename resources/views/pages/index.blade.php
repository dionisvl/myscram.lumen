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


                    <form action="/scram/register" class="js_register" style="border: 1px solid black; padding:5px;">
                        <h3>Регистрация</h3>
                        {{ csrf_field() }}
                        <input type="hidden" value="register" name="form_type">
                        <input type="text" placeholder="your login" name="user_login" >
                        <input type="text" placeholder="your password" name="user_password"><br>

                        <label for="algo_select"> Алгоритм хеширования: </label>
                        <select name="algo" id="algo_select">
                            <option disabled>Выберите алгоритм хеширования</option>
                            <option value="sha256">SHA256</option>
                            <option value="sha512" selected>SHA512</option>
                            <option value="sha3">SHA3</option>
                        </select><br>

                        <label for="encrypter_select">Вычислитель хеша: </label>
                        <select name="encrypter" id="encrypter_select">
                            <option disabled>Выберите вычислитель хеша</option>
                            <option value="openssl" selected>openSSL</option>
                            <option value="hash">PHP Hash</option>
                        </select><br>

                        <label for="hashCount">Сколько раз хешировать пароль?:</label>
                        <input type="number" id="hashCount" name="hashCount" value="2"><br>

                        <label for="protocol_select">Версия протокола: </label>
                        <select name="protocolVer" id="protocol_select">
                            <option disabled>Выберите версию протокола</option>
                            <option value="1">1</option>
                            <option value="2" selected>2</option>
                        </select><br>

                        <input type="submit" class="form_submit" value="register">
                    </form>
                    <br>

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

                    <form action="/scram/verifyNonce" class="js_auth" style="border: 1px solid black; padding:5px;">
                        <h3>Авторизация</h3>
                        {{ csrf_field() }}
                        <input type="hidden" value="auth" name="form_type">
                        <input type="text" placeholder="your login" name="user_login" >
                        <input type="text" placeholder="your password" name="user_password"><br>
{{--                        <input type="hidden" name="client_proof" value="asdfasdf">--}}
                        <label for="algo_select"> Алгоритм хеширования: </label>
                        <select name="algo" id="algo_select">
                            <option disabled>Выберите алгоритм хеширования</option>
                            <option value="sha256">SHA256</option>
                            <option value="sha512" selected>SHA512</option>
                            <option value="sha3">SHA3</option>
                        </select><br>

                        <label for="encrypter_select">Вычислитель хеша: </label>
                        <select name="encrypter" id="encrypter_select">
                            <option disabled>Выберите вычислитель хеша</option>
                            <option value="openssl" selected>openSSL</option>
                            <option value="hash">PHP Hash</option>
                        </select><br>

                        <label for="hashCount">Сколько раз хешировать пароль?:</label>
                        <input type="number" id="hashCount" name="hashCount" value="2"><br>

                        <label for="protocol_select">Версия протокола: </label>
                        <select name="protocolVer" id="protocol_select">
                            <option disabled>Выберите версию протокола</option>
                            <option value="1">1</option>
                            <option value="2" selected>2</option>
                        </select><br>

                        <button type="submit" class="form_submit">Authorize</button>
                    </form>

                    <br>
                    <div id="msg">message will be here</div>
                    <script>
                        function strXor( a, b ) {
                            let len = Math.min( a.length, b.length );
                            let arr = new Array( len );
                            for( var i = 0; i < len; ++i )
                                arr[i] = a.charCodeAt(i) ^ b.charCodeAt(i);
                            return String.fromCharCode.apply( null, arr );
                        }

                        let algo = '';
                        let hashCount = 0;
                        let encrypter = 'openssl';
                        let protocolVer = 2;

                        /** Функция для многократного хеширования */
                        function hashPassword(data){
                            var i = 0;
                            while (i<hashCount){
                                data = sha(data);
                                i++;
                            }
                            return data;
                        }

                        function sha( data ) {
                            // let favoriteAlgo = 'sha512';
                            // return eval(favoriteAlgo)(data);
                            let favoriteAlgo = '';
                            switch (algo) {
                                case 'sha256':
                                    favoriteAlgo = 'SHA-256';
                                    break;
                                case 'sha512':
                                    favoriteAlgo = 'SHA-512';
                                    break;
                                default:
                                    alert( 'Выбран неверный алгоритм хеширования' );
                            }

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

                            algo = t.find("#algo_select :selected").val();
                            hashCount = parseInt(t.find("input[name=hashCount]").val(), 10);
                            console.log('hashCount: '+hashCount);

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
                                            let client_proof = strXor(sha(password), sha(server_nonce + hashPassword(password)));

                                            /*
                                            console.log('hashCount: '+hashCount);
                                            console.log('right_part: '+sha(server_nonce + hashPassword(password)));
                                            console.log('client_proof: '+client_proof);
                                            console.log('client_proof B64: '+btoa(client_proof));
                                            console.log('server_nonce: '+server_nonce);
                                            console.log('sha(password): '+sha(password));
                                            console.log('sha(sha(password)) (multi): '+hashPassword(password));*/

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

                            algo = 'sha512';
                            hashCount = 2;
                            $.ajax({
                                type: "POST",
                                url: '/scram/getnonce',
                                data: {
                                    user_login: user_login,
                                    "_token": "{{ csrf_token() }}",
                                    'algo': algo,
                                    'encrypter':encrypter,
                                    'protocolVer':protocolVer,
                                    'hashCount':hashCount,
                                },
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
                            let client_proof = strXor(sha(password), sha(server_nonce + hashPassword(password)));

                            console.log('right_part: '+sha(server_nonce + hashPassword(password)));
                            console.log('client_proof: '+client_proof);
                            console.log('client_proof B64: '+btoa(client_proof));
                            console.log('server_nonce: '+server_nonce);
                            console.log('sha(password): '+sha(password));
                            console.log('multi-sha(password): '+hashPassword(password));
                            $.ajax({
                                type: "POST",
                                url: '/scram/verifyNonce',
                                data: {
                                    client_proof: btoa(client_proof),
                                    "_token": "{{ csrf_token() }}",
                                    'algo': algo,
                                    'encrypter':encrypter,
                                    'protocolVer':protocolVer,
                                    'hashCount':hashCount
                                },
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