<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informacion de Pago - {{ ucfirst($planNombre) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @include('a.css.cliente.pago')
</head>
<body>
    @include('componentes.navbar2')

    <main class="main-container">
        <div class="payment-header">
            <a href="{{ route('clientes.home') }}" class="payment-kicker">
                <i class="fas fa-arrow-left"></i>
                <span>Escoger otro plan</span>
            </a>
            <h1 class="payment-title">INFORMACION DE PAGO</h1>
            <div class="plan-name">{{ ucfirst($planNombre) }}</div>
            <div class="payment-mosaic" aria-hidden="true">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
        </div>

        <div class="payment-container">
            <div class="payment-summary-column">
                <div class="payment-summary">
                    <h3 class="summary-title">Resumen de tu compra</h3>
                    <div class="summary-details">
                        <span class="summary-label">Plan seleccionado:</span>
                        <span class="summary-value">{{ ucfirst($planNombre) }}</span>
                    </div>
                    <div class="summary-details">
                        <span class="summary-label">Precio:</span>
                        <span class="summary-value">{{ number_format($planPrecio, 2) }} {{ $planMoneda === 'BS' ? 'Bs' : '$' }}</span>
                    </div>
                    <div class="total-amount">
                        Total a pagar: {{ number_format($planPrecio, 2) }} {{ $planMoneda === 'BS' ? 'Bs' : '$' }}
                    </div>
                </div>
            </div>

            <div class="payment-options-column">
                <div class="payment-options">
                    <section class="payment-option">
                        <div class="option-header">
                            <input type="checkbox" id="qr-payment" name="payment-method">
                            <label for="qr-payment" class="option-title">
                                <i class="fas fa-qrcode"></i> Pago con QR
                            </label>
                        </div>
                        <div class="option-content" id="qr-content">
                            <div id="qr-form" class="billing-form">
                                <div class="field-group field-wide">
                                    <label for="business-name">Razon social</label>
                                    <input id="business-name" type="text" maxlength="255" value="{{ auth()->user()->name }}" autocomplete="organization">
                                </div>
                                <div class="field-group">
                                    <label for="document-type">Tipo de documento</label>
                                    <select id="document-type">
                                        <option value="1">Carnet de identidad</option>
                                        <option value="2">Carnet de extranjeria</option>
                                        <option value="3">Pasaporte</option>
                                        <option value="4">Otro documento</option>
                                        <option value="5">NIT</option>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label for="document-number">Numero de documento</label>
                                    <input id="document-number" type="text" inputmode="numeric" maxlength="50" autocomplete="off">
                                </div>
                                <div class="field-group">
                                    <label for="document-complement">Complemento</label>
                                    <input id="document-complement" type="text" maxlength="20" placeholder="Opcional">
                                </div>
                                <div class="field-group">
                                    <label for="document-extension">Extension</label>
                                    <select id="document-extension">
                                        <option value="">Sin extension</option>
                                        <option value="LP">LP</option>
                                        <option value="CB">CB</option>
                                        <option value="SC">SC</option>
                                        <option value="OR">OR</option>
                                        <option value="PT">PT</option>
                                        <option value="TJ">TJ</option>
                                        <option value="CH">CH</option>
                                        <option value="BE">BE</option>
                                        <option value="PD">PD</option>
                                    </select>
                                </div>
                            </div>

                            <div id="qr-result" class="qr-code" hidden>
                                <img id="libelula-qr" alt="Codigo QR de pago generado por Libelula" hidden>
                                <p id="qr-status">Escanea el QR con tu aplicacion bancaria. Confirmaremos el pago automaticamente.</p>
                                <a id="payment-link" class="payment-link" target="_blank" rel="noopener" hidden>
                                    <i class="fas fa-arrow-up-right-from-square"></i> Abrir pasarela de pago
                                </a>
                            </div>
                        </div>
                    </section>

                    <section class="payment-option">
                        <div class="option-header">
                            <input type="checkbox" id="physical-payment" name="payment-method">
                            <label for="physical-payment" class="option-title">
                                <i class="fas fa-building-columns"></i> Pago fisico
                            </label>
                        </div>
                        <div class="option-content" id="physical-content">
                            <div class="physical-payment">
                                <p><i class="fas fa-info-circle"></i> Se habilitara la plataforma cuando se confirme el pago.</p>
                                <div class="payment-code" id="payment-code">Pendiente de generar</div>
                                <p>Presenta este codigo al pagar en nuestras oficinas.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <button class="done-btn" id="done-btn" type="button">
            <i class="fas fa-check-circle"></i> Continuar
        </button>
    </main>

    <div class="modal" id="success-modal" role="dialog" aria-modal="true" aria-labelledby="success-title">
        <div class="modal-content">
            <h2 class="modal-title" id="success-title">
                <i class="fas fa-check-circle"></i> Pago confirmado
            </h2>
            <p>Libelula confirmo tu pago y tu plan ya se encuentra activo.</p>
            <button class="modal-btn" id="continue-btn" type="button">
                <i class="fas fa-arrow-right"></i> Continuar
            </button>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qrCheckbox = document.getElementById('qr-payment');
    const physicalCheckbox = document.getElementById('physical-payment');
    const qrContent = document.getElementById('qr-content');
    const physicalContent = document.getElementById('physical-content');
    const doneBtn = document.getElementById('done-btn');
    const qrForm = document.getElementById('qr-form');
    const qrResult = document.getElementById('qr-result');
    const qrImage = document.getElementById('libelula-qr');
    const paymentLink = document.getElementById('payment-link');
    const qrStatus = document.getElementById('qr-status');
    let transaction = @json($libelulaTransaction);
    let pollTimer = null;

    const buttonContent = (icon, label) => `<i class="fas ${icon}"></i> ${label}`;

    function selectMethod(method) {
        const qr = method === 'qr';
        qrCheckbox.checked = qr;
        physicalCheckbox.checked = !qr;
        qrContent.style.display = qr ? 'block' : 'none';
        physicalContent.style.display = qr ? 'none' : 'block';
        doneBtn.innerHTML = buttonContent(qr ? 'fa-qrcode' : 'fa-check-circle', qr ? (transaction ? 'Verificar pago' : 'Generar QR') : 'Generar codigo');
    }

    function renderTransaction(data) {
        transaction = data;
        qrForm.hidden = true;
        qrResult.hidden = false;
        if (data.qr_url) {
            qrImage.src = data.qr_url;
            qrImage.hidden = false;
        } else {
            qrImage.hidden = true;
        }
        if (data.payment_url) {
            paymentLink.href = data.payment_url;
            paymentLink.hidden = false;
        }
        qrStatus.textContent = 'QR generado. Esperando la confirmacion de Libelula...';
        doneBtn.innerHTML = buttonContent('fa-rotate', 'Verificar pago');
        startPolling();
    }

    async function readJson(response) {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const validation = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(validation || data.message || 'No fue posible procesar la solicitud.');
        }
        return data;
    }

    async function checkStatus() {
        if (!transaction?.status_url) return;
        try {
            const data = await readJson(await fetch(transaction.status_url, {
                headers: { 'Accept': 'application/json' }
            }));
            transaction = data;
            if (data.status === 'paid') {
                clearInterval(pollTimer);
                pollTimer = null;
                document.getElementById('success-modal').style.display = 'flex';
            } else if (data.status === 'expired' || data.status === 'failed') {
                clearInterval(pollTimer);
                pollTimer = null;
                qrStatus.textContent = 'Este QR ya no esta disponible. Genera uno nuevo.';
                transaction = null;
                qrForm.hidden = false;
                doneBtn.innerHTML = buttonContent('fa-qrcode', 'Generar QR');
            }
        } catch (error) {
            console.error(error);
        }
    }

    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(checkStatus, 5000);
    }

    qrCheckbox.addEventListener('change', () => {
        if (qrCheckbox.checked) selectMethod('qr');
        else qrContent.style.display = 'none';
    });

    physicalCheckbox.addEventListener('change', () => {
        if (physicalCheckbox.checked) selectMethod('fisico');
        else physicalContent.style.display = 'none';
    });

    document.getElementById('continue-btn').addEventListener('click', () => {
        window.location.href = @json(route('clientes.dashboard'));
    });

    doneBtn.addEventListener('click', async () => {
        if (!qrCheckbox.checked && !physicalCheckbox.checked) {
            showCustomAlert('Selecciona un metodo de pago.');
            return;
        }

        doneBtn.disabled = true;
        doneBtn.innerHTML = buttonContent('fa-spinner fa-spin', 'Procesando...');

        try {
            if (qrCheckbox.checked) {
                if (transaction) {
                    await checkStatus();
                    return;
                }

                const payload = {
                    document_type_code: document.getElementById('document-type').value,
                    document_number: document.getElementById('document-number').value.trim(),
                    document_complement: document.getElementById('document-complement').value.trim() || null,
                    document_extension: document.getElementById('document-extension').value || null,
                    business_name: document.getElementById('business-name').value.trim()
                };
                const data = await readJson(await fetch(@json(route('pago.libelula.crear', $plan)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }));
                renderTransaction(data);
            } else {
                const data = await readJson(await fetch(@json(route('pago.procesar', $plan)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ metodo_pago: 'fisico' })
                }));
                document.getElementById('payment-code').textContent = data.codigo;
                showCustomAlert(data.message);
            }
        } catch (error) {
            showCustomAlert(error.message);
        } finally {
            doneBtn.disabled = false;
            if (qrCheckbox.checked) {
                doneBtn.innerHTML = buttonContent(transaction ? 'fa-rotate' : 'fa-qrcode', transaction ? 'Verificar pago' : 'Generar QR');
            } else {
                doneBtn.innerHTML = buttonContent('fa-check-circle', 'Generar codigo');
            }
        }
    });

    function showCustomAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'payment-alert';
        const icon = document.createElement('i');
        icon.className = 'fas fa-circle-exclamation';
        const text = document.createElement('span');
        text.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Cerrar');
        close.innerHTML = '<i class="fas fa-times"></i>';
        close.addEventListener('click', () => alertDiv.remove());
        alertDiv.append(icon, text, close);
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 6000);
    }

    if (transaction) {
        selectMethod('qr');
        renderTransaction(transaction);
    }
});
</script>
</body>
</html>
