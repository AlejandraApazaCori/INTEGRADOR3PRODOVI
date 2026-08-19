<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña | PRODOVI</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box} body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:#f1f0f2;color:#302834;font-family:Arial,Helvetica,sans-serif}.reset-card{width:min(520px,100%);overflow:hidden;border:1px solid #ded7e1;border-radius:6px;background:#fff;box-shadow:0 24px 65px rgba(41,30,46,.16)}.reset-header{padding:26px 28px;border-bottom:5px solid #ee9f2b;background:#242426;color:#fff}.reset-header span{display:block;margin-bottom:7px;color:#ee9f2b;font-size:11px;font-weight:900;letter-spacing:.13em}.reset-header h1{margin:0;font-size:24px}.reset-body{padding:28px}.reset-intro{margin:0 0 22px;color:#766c7a;font-size:14px;line-height:1.6}.reset-error{margin-bottom:16px;padding:12px;border-left:4px solid #b63b3b;background:#fff1f1;color:#982b2b;font-size:13px}.reset-field{margin-bottom:16px}.reset-field label{display:block;margin-bottom:7px;color:#665b6b;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.reset-field div{position:relative}.reset-field input{width:100%;height:47px;padding:0 42px 0 13px;border:1px solid #d8cfdc;border-radius:4px;font-size:14px;outline:none}.reset-field input:focus{border-color:#5b2b76;box-shadow:0 0 0 3px #eee6f2}.reset-field i{position:absolute;right:14px;top:50%;color:#5b2b76;transform:translateY(-50%)}.reset-email{padding:12px;border-left:4px solid #117e8c;background:#eef7f8;color:#4d6167;font-size:13px}.reset-button{width:100%;margin-top:7px;padding:13px;border:0;border-radius:4px;background:#5b2b76;color:#fff;font-weight:900;cursor:pointer}.reset-button:hover{background:#6b3587}@media(prefers-color-scheme:dark){body{background:#141216;color:#eee}.reset-card{border-color:#403943;background:#1e1b21}.reset-intro,.reset-field label{color:#b4abb8}.reset-field input{border-color:#4a434e;background:#29252c;color:#f1edf3}.reset-email{background:#173136;color:#9bd1d6}.reset-error{background:#3a2022;color:#efaaaa}}
    </style>
</head>
<body>
    <main class="reset-card">
        <header class="reset-header"><span>CONFIRMACIÓN SEGURA</span><h1>Crea tu nueva contraseña</h1></header>
        <div class="reset-body">
            <p class="reset-intro">Elige una contraseña de al menos 8 caracteres. Al guardarla, el enlace del correo dejará de ser válido.</p>
            @if($errors->any())<div class="reset-error">{{ $errors->first() }}</div>@endif
            <div class="reset-email"><i class="fas fa-envelope"></i> {{ $email }}</div>
            <form method="POST" action="{{ route('clientes.password.reset', ['token' => $token]) }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="reset-field"><label for="password">Nueva contraseña</label><div><input id="password" name="password" type="password" required autocomplete="new-password"><i class="fas fa-key"></i></div></div>
                <div class="reset-field"><label for="password_confirmation">Confirmar contraseña</label><div><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"><i class="fas fa-check"></i></div></div>
                <button class="reset-button" type="submit"><i class="fas fa-shield-halved"></i> Guardar nueva contraseña</button>
            </form>
        </div>
    </main>
</body>
</html>
