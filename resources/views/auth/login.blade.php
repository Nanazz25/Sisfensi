<!doctype html>
<html lang="en">

<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Iconic Bootstrap 4.5.0 Admin Template">
    <meta name="author" content="WrapTheme, design by: ThemeMakker.com">

    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

</head>

<body data-theme="light" class="font-nunito">
    <div id="wrapper" class="theme-cyan">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="top">
                        <h3 class="text-white font-weight-bold"><i class="fa fa-clock-o mr-2"></i> SISFENSI</h3>
                    </div>
                    <div class="card">
                        <div class="header">
                            <p class="lead">Login to your account</p>
                        </div>
                        <div class="body">
                            @if ($errors->any())
                                <p style="color:red">{{ $errors->first() }}</p>
                            @endif

                            <form method="POST" action="{{ route('login.post') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="signin-email" class="control-label sr-only">Email</label>
                                    <input type="email" class="form-control" id="signin-email" name="email"
                                        placeholder="Email" required>
                                </div>
                                <div class="form-group">
                                    <label for="signin-password" class="control-label sr-only">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="signin-password" name="password"
                                            placeholder="Password" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                                style="border-color: #ced4da; border-left: none;">
                                                <i class="fa fa-eye-slash text-muted"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                    <label class="fancy-checkbox element-left">
                                        <input type="checkbox" name="remember" value="1">
                                        <span>Remember me</span>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg btn-block">LOGIN</button>
                                <div class="bottom">
                                    <span class="helper-text mt-3 m-b-10"><i class="fa fa-lock"></i> Lupa Password? Hubungi IT Support</span>
                                    <span>Belum punya akun? <br> Hubungi <a href="javascript:void(0);">Admin Sekolah</a> untuk akses masuk.</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.toggle-password').addEventListener('click', function () {
            const input = document.getElementById('signin-password');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    </script>
</body>

</html>