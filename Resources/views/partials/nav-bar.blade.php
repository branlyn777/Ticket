<div class="d-flex justify-content-end ml-auto">
	@if(\App\Helpers\HostHelper::isScrivania())
	<a  href="{{ route('ticket-tokens.index') }}" class="btn btn-{{ Request::is('module/ticket-tokens') ?  'primary text-white' : 'outline-primary' }} mr-2"><i class=" icon-settings"></i> {{ __('ticket::main.generate_token') }}</a>
	<a href="{{ route('ticket-settings.index') }}" class="btn btn-{{ Request::is('module/ticket-settings') ?  'primary text-white' : 'outline-primary' }} "><i class=" icon-settings"></i> Impostazioni</a>
	@endif
	@if(!Request::is('module/tickets') and !Request::is('module/tickets/*'))
		<a href="{{ route('project-settings.index') }}" class="btn btn-primary text-white ml-2"><i class="icon-arrow-left"></i> {{ __('main.back') }}</a>
	@endif
</div>



