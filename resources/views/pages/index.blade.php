@extends('layout')

@section('content')
{{--    //laravel blade csrf token--}}
{{--    <meta name="csrf-token" content="{{ csrf_token() }}">--}}

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

                            let user_login = t.find("input[name=user_login]").val();
                            let user_password = t.find("input[name=user_password]").val();
                            let algo = t.find("#algo_select :selected").val();
                            let hashCount = parseInt(t.find("input[name=hashCount]").val(), 10);
                            let encrypter = t.find("#encrypter_select :selected").val();
                            let protocolVer = t.find("#protocol_select :selected").val();

                            let csrf_token = $("meta[name=csrf-token]").attr("content");

                            function registration(action,user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token){
                                let answer;

                                $.ajax({
                                    type: "POST",
                                    url: action,
                                    data: {
                                        user_login: user_login,
                                        user_password: user_password,
                                        'algo': algo,
                                        'encrypter':encrypter,
                                        'protocolVer':protocolVer,
                                        'hashCount':hashCount,
                                        "_token": "{{ csrf_token() }}",
                                        //"_token": csrf_token,
                                    },
                                    success: function (response) {
                                        try {
                                            let data = JSON.parse(response);
                                            if (data['status'] === true) {
                                                console.log('Ok');
                                                answer = data['msg'];
                                            } else {
                                                console.log('NO Ok: '+response);
                                                answer = data['msg'];
                                            }
                                        } catch (e) {
                                            console.log('Error: '+ e);
                                            console.log('response: '+ response);
                                            answer = 'Error: '+ e;
                                        }
                                    }
                                });

                                return answer;
                            }
                            msg.innerHTML = registration(user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token);
                        });




                        $(".js_auth").on('submit', function (event) {
                            event.preventDefault();
                            let t = $(this);
                            let action = t.attr('action');
                            let msg = document.getElementById('msg');

                            let user_login = t.find("input[name=user_login]").val();
                            let user_password = t.find("input[name=user_password]").val();
                            let algo = t.find("#algo_select :selected").val();
                            let hashCount = parseInt(t.find("input[name=hashCount]").val(), 10);
                            let encrypter = t.find("#encrypter_select :selected").val();
                            let protocolVer = t.find("#protocol_select :selected").val();

                            let csrf_token = $("meta[name=csrf-token]").attr("content");
                            let server_nonce = 'empty nonce';
                            let answer = [];


                            answer = get_auth_nonce(action,user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token);
                            if (typeof answer['server_nonce'] === 'undefined' || answer['server_nonce'] === null) {
                                msg.innerHTML = '';
                                msg.innerHTML = answer['msg'];
                            } else {
                                msg.innerHTML = '';
                                msg.innerHTML = answer['msg'];
                                let check_auth_answer = check_auth(action,user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token,answer['server_nonce'],answer['client_proof']);
                                msg.innerHTML = check_auth_answer['msg'];
                            }


                        });



                        function get_auth_nonce(action,user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token){//сначала получим серверный нонс
                            let answer = {};
                            $.ajax({
                                type: "POST",
                                url: '/scram/getnonce',
                                data: {
                                    user_login: user_login,
                                    user_password: user_password,
                                    'algo': algo,
                                    'encrypter':encrypter,
                                    'protocolVer':protocolVer,
                                    'hashCount':hashCount,
                                    //"_token": csrf_token,
                                    "_token": "{{ csrf_token() }}",
                                },

                                success: function (response) {
                                    try {
                                        let data = JSON.parse(response);
                                        if (data['status'] === true) {
                                            answer.server_nonce = data['server_nonce'];
                                            answer.msg = (data['msg'] + ' nonce='+data['server_nonce']);
                                            answer.client_proof = btoa(strXor(sha(user_password), sha(data['server_nonce'] + hashPassword(user_password))));
                                            // console.log(answer);
                                            return answer;
                                            /*
                                            console.log('hashCount: '+hashCount);
                                            console.log('right_part: '+sha(server_nonce + hashPassword(password)));
                                            console.log('client_proof: '+client_proof);
                                            console.log('client_proof B64: '+btoa(client_proof));
                                            console.log('server_nonce: '+server_nonce);
                                            console.log('sha(password): '+sha(password));
                                            console.log('sha(sha(password)) (multi): '+hashPassword(password));
                                            */
                                        } else {
                                            answer['msg'] = data['msg'];
                                            console.log(response);
                                        }
                                    } catch (e) {
                                        console.log('response: '+ response);
                                        answer['msg'] = 'Error: '+ e;
                                    }
                                }
                            });

                            // console.log(answer);
                            return answer;
                        }


                        function check_auth(action,user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token,server_nonce,client_proof){
                            let answer;
                            $.ajax({
                                type: "POST",
                                url: action,
                                data: {
                                    user_login: user_login,
                                    user_password: user_password,
                                    'algo': algo,
                                    'encrypter':encrypter,
                                    'protocolVer':protocolVer,
                                    'hashCount':hashCount,
                                    "_token": "{{ csrf_token() }}",
                                    'server_nonce': server_nonce,
                                    'client_proof':client_proof
                                },

                                processData: false,
                                contentType: false,
                                dataType: "json",
                                success: function (response) {
                                    try {
                                        let data = JSON.parse(response);
                                        if (data['status'] === true) {
                                            console.log('Ok');
                                            answer = data['msg'];
                                        } else {
                                            console.log('NO Ok: '+response);
                                            answer = data['msg'];
                                        }
                                    } catch (e) {
                                        console.log('Error: '+ e);
                                        console.log('response: '+ response);
                                        answer = 'Error: '+ e;
                                    }
                                }
                            });
                            return answer;
                        }
                    </script>



                    <h3>Тестовая проверка SCRAM авторизации одним кликом:<button id="check_auth_fast">check auth</button></h3>

                    <div id="server_nonce_msg">server nonce will be here</div>
                    <div id="check_auth_msg">auth_msg will be here</div>

                    <script>
                        $("#check_auth_fast").on('click',function(event) {
                            let msg = document.getElementById('server_nonce_msg');
                            let csrf_token = $("meta[name=csrf-token]").attr("content");
                            let user_login = 'test_login';
                            let user_password = 'HELLO_WORLD';

                            algo = 'sha512';
                            hashCount = 2;
                            encrypter = 'openssl';
                            protocolVer = 2;
                            // console.log(csrf_token);

                            let answer = get_auth_nonce('/scram/getNonce',user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token);//сначала получим server_nonce путем запроса к серверу
                            console.log(answer);
                            console.log(answer.msg);


                            if (typeof answer['server_nonce'] === 'undefined' || answer['server_nonce'] === null) {
                                msg.innerHTML = '';
                                msg.innerHTML = answer['msg'];
                            } else {
                                msg.innerHTML = '';
                                msg.innerHTML = answer['msg'];
                                let check_auth_answer = check_auth('/scram/verifyNonce',user_login,user_password,algo,encrypter,protocolVer,hashCount,csrf_token,answer['server_nonce'],answer['client_proof']);
                                msg.innerHTML = check_auth_answer['msg'];
                            }
                        });
                    </script>

                </div>
                @include('pages._sidebar')
            </div>
        </div>
    </div>
    <!-- end main content-->
@endsection



{{--// Складываем два числа удаленно--}}
{{--let resultA, resultB, resultC;--}}

{{--function get_auth_nonce(action, user_login) {--}}

{{--const payload = {--}}
{{--user_login: user_login--}}
{{--}--}}

{{--return fetch(action+'?user_login='+user_login)--}}


{{--.then(x => x.json()--}}
{{--);--}}
{{--}--}}
{{--console.log(11111111111);--}}


{{--get_auth_nonce('http://scram/scram/getnonce', 'test_login')--}}
{{--.then(success => {--}}
{{--resultA = success;--}}
{{--console.log(success.status);--}}
{{--console.log('total: ' + success);--}}
{{--return resultA;--}}
{{--})--}}