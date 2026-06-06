<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Lá Số Tứ Trụ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #dd1212 0%, #970303 100%);;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .input-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .error-message {
            color: #dc2626;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="mb-4">
                <i class="fas fa-user-lock text-5xl mb-4"></i>
            </div>
            <h1>ĐĂNG NHẬP</h1>
            <p>Vui lòng đăng nhập để tiếp tục</p>
        </div>

        <!-- Form -->
        <div class="p-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-purple-500"></i>
                        Email
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Nhập email của bạn"
                           required
                           autofocus>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-purple-500"></i>
                        Mật khẩu
                    </label>
                    <div class="relative">
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Nhập mật khẩu"
                               required>
                        <button type="button" 
                                id="togglePassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-6 flex items-center">
                    <input type="checkbox" 
                           name="remember" 
                           id="remember"
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <label for="remember" class="ml-2 text-sm text-gray-700">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="btn-login text-white font-bold py-4 px-6 rounded-lg w-full flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Đăng Nhập
                </button>
            </form>

            <!-- Footer -->
            <div class="text-center mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Thông tin đăng nhập được bảo mật an toàn
                </p>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const eyeIcon = $('#eyeIcon');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Form validation
            $('#loginForm').on('submit', function(e) {
                const email = $('input[name="email"]').val();
                const password = $('input[name="password"]').val();

                if (!email || !password) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin!');
                    return false;
                }

                // Show loading
                const $submitBtn = $(this).find('button[type="submit"]');
                const originalHtml = $submitBtn.html();
                $submitBtn.prop('disabled', true);
                $submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...');
            });
        });
    </script>
</body>

</html>

