<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Models\Enums\UserState;
use App\Models\User;
use App\Repositories\AirlineRepository;
use App\Repositories\AirportRepository;
use App\Services\UserService;
use App\Support\Countries;
use App\Support\Timezonelist;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    private $airlineRepo;
    private $airportRepo;
    private $userService;

    /**
     * RegisterController constructor.
     *
     * @param AirlineRepository $airlineRepo
     * @param AirportRepository $airportRepo
     * @param UserService       $userService
     */
    public function __construct(
        AirlineRepository $airlineRepo,
        AirportRepository $airportRepo,
        UserService $userService
    ) {
        $this->airlineRepo = $airlineRepo;
        $this->airportRepo = $airportRepo;
        $this->userService = $userService;
        $this->middleware('guest');

        $this->redirectTo = config('phpvms.registration_redirect');
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    public function showRegistrationForm()
    {
        $airports = $this->airportRepo->selectBoxList(false, setting('pilots.home_hubs_only'));
        $airlines = $this->airlineRepo->selectBoxList();

        return view('auth.register', [
            'airports'  => $airports,
            'airlines'  => $airlines,
            'countries' => Countries::getSelectList(),
            'timezones' => Timezonelist::toArray(),
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name'            => 'required|max:255',
            'email'           => 'required|email|max:255|unique:users, email',
            'airline_id'      => 'required',
            'home_airport_id' => 'required',
            'password'        => 'required|min:5|confirmed',
            'toc_accepted'    => 'accepted',
        ];

        if (config('captcha.enabled')) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        return Validator::make($data, $rules);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $opts
     *
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @return User
     */
    protected function create(array $opts)
    {
        // Default options
        $opts['password'] = Hash::make($opts['password']);

        // Convert transfer hours into minutes
        if (isset($opts['transfer_time'])) {
            $opts['transfer_time'] *= 60;
        }

        $user = $this->userService->createUser($opts);

        Log::info('User registered: ', $user->toArray());

        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function register(CreateUserRequest $request)
    {
        $user = $this->create($request->all());
        if ($user->state === UserState::PENDING) {
            return view('auth.pending');
        }

        $this->guard()->login($user);

        return redirect('/dashboard');
    }
}
