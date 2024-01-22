<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Events\SuccessfulLogin;
use App\Events\UnsuccessfulLogin;
use App\Http\Responses\RegisterResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            RegisterResponseContract::class,
            RegisterResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        //        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn () => view('auth.login'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('username', $request->input('username'))->first();

            if ($user?->status === User::STATUS_INACTIVE) {
                $error = [Fortify::username() => 'Akun belum aktif!'];

                event(new UnsuccessfulLogin(['username' => $request->input('username'), ...compact('error')]));
                throw ValidationException::withMessages($error);
            }

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                $error = [Fortify::username() => 'Data kredensial tidak sesuai.'];

                event(new UnsuccessfulLogin(['username' => $request->input('username'), ...compact('error')]));
                throw ValidationException::withMessages($error);
            }

            event(new SuccessfulLogin(['username' => $request->input('username')]));

            return $user;
        });

        if (filter_var(global_config('registration_availability', false), FILTER_VALIDATE_BOOL)) {
            Fortify::registerView(fn () => view('auth.register'));
            Fortify::createUsersUsing(CreateNewUser::class);
        } else {
            Fortify::registerView(fn () => abort(Response::HTTP_NOT_FOUND));
        }

        //        RateLimiter::for('two-factor', function (Request $request) {
        //            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        //        });
    }
}
