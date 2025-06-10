<div class="card border border-base-300">
    <div class="card-body bg-base-200 text-4xl">
        <h2 class="card-title">{{ $user->name }}</h2>
    </div>
    <figure>
        {{-- ユーザーのメールアドレスをもとにGravatarを取得して表示 --}}
        <div>Email : {{ $user->email }}</div>
    </figure>
</div>