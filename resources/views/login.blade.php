@extends('layouts.login')

@push('styles')
  <style>
    .show-hide .show:before {
      content: 'Show';
    }

    .show-hide .hide:before {
      content: 'Hide';
    }

    /* Smooth Spinner Transition */
    .custom-spinner-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 0;
      opacity: 0;
      margin-right: 0;
      visibility: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }

    .custom-spinner-wrapper.loading {
      width: 1.2rem;
      opacity: 1;
      margin-right: 0.5rem;
      visibility: visible;
    }

    /* Ensure spinner border doesn't leak out */
    .custom-spinner-wrapper .spinner-border {
      width: 14px;
      height: 14px;
      border-width: 0.15em;
    }

    /* Login Logo Style */
    .login-logo-container {
      margin-bottom: 30px;
      display: flex;
      justify-content: flex-start;
    }

    .login-logo-container img {
      max-height: 50px;
      width: auto;
      object-fit: contain;
    }
  </style>
@endpush

@section('title')
  Login - KORPS
@endsection
@section('content')

  {{-- {{ dd(Auth::user()) }} --}}
  <div class="container-fluid">
    <div aria-live="polite" aria-atomic="true" class="position-relative">
      <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toasts will be appended here -->
      </div>
    </div>
    <div class="row">
      <div class="col-xl-5"><img class="bg-img-cover bg-center"
          src="{{ asset('admin/assets/images/login/logi-bg.webp') }}" alt="looginpage"></div>
      <div class="col-xl-7 p-0">
        <div class="login-card">
          <div class="mb-4">
            <div class="login-logo-container">
                <img class="img-fluid" src="{{ asset('admin/assets/images/logo/korps-sync-crm-logo-white.png') }}" alt="logo">
            </div>
            <div class="login-main">
              <form class="theme-form" action="{{ route('login') }}" method="POST">
                @csrf
                <h4 class="mb-2">Sign in to account</h4>
                {{-- <p>Enter your email & password to login</p> --}}
                <div class="form-group">
                  <label class="col-form-label" for="email">Email Address</label>
                  <input class="form-control" type="email" name="email" id="email" required placeholder="Test@gmail.com" autocomplete="email" autofocus>
                </div>
                <div class="form-group">
                  <label class="col-form-label" for="password">Password</label>
                  <div class="form-input position-relative">
                    <input class="form-control" type="password" name="password" id="password" required placeholder="*********" autocomplete="current-password">
                    <div class="show-hide"><span class="show"></span></div>
                  </div>
                </div>
                <div class="form-group mb-0">
                  <div class="checkbox p-0">
                    <input id="checkbox1" type="checkbox" name="remember">
                    <label class="text-muted" for="checkbox1">Remember password</label>
                  </div>
                  <div class="text-end mt-3">
                    <button class="btn btn-primary btn-block w-100" type="submit" id="submit-btn" style="height: 48px;">
                      <div class="d-flex align-items-center justify-content-center">
                        <div class="custom-spinner-wrapper" id="btn-spinner-wrapper">
                          <span class="spinner-border" role="status" aria-hidden="true"></span>
                        </div>
                        <span id="btn-text">Sign in</span>
                      </div>
                    </button>
                  </div>
                </div>
                {{-- <h6 class="text-muted mt-4 or">Or Sign in with</h6>
                <div class="social mt-4">
                  <div class="btn-showcase"><a class="btn btn-light" href="https://www.linkedin.com/login"
                      target="_blank"><i class="txt-linkedin" data-feather="linkedin"></i> LinkedIn </a><a
                      class="btn btn-light" href="https://twitter.com/login?lang=en" target="_blank"><i
                        class="txt-twitter" data-feather="twitter"></i>twitter</a><a class="btn btn-light"
                      href="https://www.facebook.com/" target="_blank"><i class="txt-fb"
                        data-feather="facebook"></i>facebook</a></div>
                </div>
                <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="sign-up.html">Create Account</a>
                </p> --}}
                <script>
                  (function () {
                    'use strict';
                    window.addEventListener('load', function () {
                      // Fetch all the forms we want to apply custom Bootstrap validation styles to
                      var forms = document.getElementsByClassName('needs-validation');
                      // Loop over them and prevent submission
                      var validation = Array.prototype.filter.call(forms, function (form) {
                        form.addEventListener('submit', function (event) {
                          if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                          }
                          form.classList.add('was-validated');
                        }, false);
                      });
                    }, false);
                  })();
                </script>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const toastContainer = document.querySelector('.toast-container');

        <?php if ($errors->any()): ?>
        <?php  foreach ($errors->all() as $error): ?>
        showToast('{{ $error }}', 'danger');
        <?php  endforeach; ?>
        <?php endif; ?>

        function showToast(message, type) {
          const toastHtml = `
                  <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="d-flex">
                          <div class="toast-body">
                              ${message}
                          </div>
                          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                      </div>
                  </div>
              `;
          const toastElement = document.createRange().createContextualFragment(toastHtml).children[0];
          toastContainer.append(toastElement);
          const toast = new bootstrap.Toast(toastElement);
          toast.show();
        }
      });
    </script>
@endsection

  @push('scripts')
    <script>
      (function ($) {
        "use strict";
        $(document).ready(function () {
          $(".show-hide").on('click', function () {
            var _this = $(this);
            var passwordInput = _this.parent().find('input');
            if (passwordInput.attr("type") == "password") {
              passwordInput.attr("type", "text");
              _this.find('span').removeClass("show");
              _this.find('span').addClass("hide");
            } else {
              passwordInput.attr("type", "password");
              _this.find('span').removeClass("hide");
              _this.find('span').addClass("show");
            }
          });

          // Login Loader Logic
          $('.theme-form').on('submit', function () {
            const $btn = $('#submit-btn');
            const $spinnerWrapper = $('#btn-spinner-wrapper');

            $btn.prop('disabled', true);
            $spinnerWrapper.addClass('loading');
          });
        });
      })(jQuery);
    </script>
  @endpush