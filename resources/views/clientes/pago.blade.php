@php
    $planNombre = str_replace('-', ' ', $plan);
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Pago - {{ ucfirst($planNombre) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @include('a.css.cliente.pago')
    

</head>
<body>
    @include('componentes.navbar2')
    
    <div class="main-container">
        <div class="payment-header">
            <a href="{{ route('clientes.home') }}" class="payment-kicker">
                <i class="fas fa-arrow-left"></i>
                <span>Escoger otro plan</span>
            </a>
            <h1 class="payment-title">INFORMACIÓN DE PAGO</h1>
            <div class="plan-name">{{ ucfirst($planNombre) }}</div>
            <div class="payment-mosaic" aria-hidden="true">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
        </div>
        
        <!-- Contenedor principal con dos columnas -->
        <div class="payment-container">
            <!-- Columna izquierda - Resumen de compra -->
            <div class="payment-summary-column">
                <div class="payment-summary">
                    <h3 class="summary-title">Resumen de tu compra</h3>
                    <div class="summary-details">
                        <span class="summary-label">Plan seleccionado:</span>
                        <span class="summary-value">{{ ucfirst($planNombre) }}</span>
                    </div>
                    <div class="summary-details">
                        <span class="summary-label">Precio:</span>
                        <span class="summary-value">{{ number_format($planPrecio) }} {{ $planMoneda == 'BS' ? 'Bs' : '$' }}</span>
                    </div>
                    
                    <div class="total-amount">
                        Total a pagar: {{ number_format($planPrecio) }} {{ $planMoneda == 'BS' ? 'Bs' : '$' }}
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha - Opciones de pago -->
            <div class="payment-options-column">
                <div class="payment-options">
                    <div class="payment-option">
                        <div class="option-header">
                            <input type="checkbox" id="qr-payment" name="payment-method">
                            <label for="qr-payment" class="option-title">
                                <i class="fas fa-qrcode"></i> Pago con QR
                            </label>
                        </div>
                        <div class="option-content" id="qr-content">
                            <div class="qr-code">
                                <img src="{{ asset('imagenes/qr-code-example.png') }}" alt="Código QR para pago">
                                <p>Escanea este código QR con tu aplicación de banca móvil para completar el pago</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-option">
                        <div class="option-header">
                            <input type="checkbox" id="physical-payment" name="payment-method">
                            <label for="physical-payment" class="option-title">
                                <i class="fas fa-building-columns"></i> Pago Físico
                            </label>
                        </div>
                        <div class="option-content" id="physical-content">
                            <div class="physical-payment">
                                <p><i class="fas fa-info-circle"></i> Se le habilitará la plataforma una vez complete el pago</p>
                                <div class="payment-code" id="payment-code">FIS{{ str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) }}</div>
                                <p>Ve con este código a pagar en nuestras oficina</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <button class="done-btn" id="done-btn">
            <i class="fas fa-check-circle"></i> Listo
        </button>
    </div>
    
    <div class="modal" id="success-modal">
        <div class="modal-content">
            <h2 class="modal-title">
                <i class="fas fa-check-circle"></i> ¡Pago Completado!
            </h2>
            <p>Tu pago ha sido procesado exitosamente. Ahora tienes acceso completo a tu plan.</p>
            <button class="modal-btn" id="continue-btn">
                <i class="fas fa-arrow-right"></i> Continuar
            </button>
        </div>
    </div>
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrCheckbox = document.getElementById('qr-payment');
        const physicalCheckbox = document.getElementById('physical-payment');
        const qrContent = document.getElementById('qr-content');
        const physicalContent = document.getElementById('physical-content');
        const doneBtn = document.getElementById('done-btn');
        
        qrCheckbox.addEventListener('change', function() {
            if(this.checked) {
                physicalCheckbox.checked = false;
                physicalContent.style.display = 'none';
                qrContent.style.display = 'block';
            } else {
                qrContent.style.display = 'none';
            }
        });
        
        physicalCheckbox.addEventListener('change', function() {
            if(this.checked) {
                qrCheckbox.checked = false;
                qrContent.style.display = 'none';
                physicalContent.style.display = 'block';
            } else {
                physicalContent.style.display = 'none';
            }
        });
        
        document.getElementById('continue-btn').addEventListener('click', function() {
            window.location.href = "{{ route('clientes.dashboard') }}";
        });
        doneBtn.addEventListener('click', async function() {
            if(!qrCheckbox.checked && !physicalCheckbox.checked) {
                showCustomAlert('Por favor selecciona un método de pago');
                return;
            }
            
            const metodoPago = qrCheckbox.checked ? 'qr' : 'fisico';
            doneBtn.disabled = true;
            doneBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            
            try {
                const response = await fetch("{{ route('pago.procesar', $plan) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        metodo_pago: metodoPago
                    })
                });
                
                const data = await response.json();
                
                if(!response.ok) {
                    throw new Error(data.message || 'Error en la respuesta del servidor');
                }
                
                if(data.success) {
                    if(data.metodo === 'qr') {
                        document.getElementById('success-modal').style.display = 'flex';
                    } else {
                        document.getElementById('payment-code').textContent = data.codigo;
                        showCustomAlert(data.message);
                    }
                } else {
                    throw new Error(data.message || 'Error al procesar el pago');
                }
            } catch (error) {
                console.error('Error:', error);
                showCustomAlert(error.message);
            } finally {
                doneBtn.disabled = false;
                doneBtn.innerHTML = '<i class="fas fa-check-circle"></i> Listo';
            }
        }); 

       function showCustomAlert(message) {
            const alertDiv = document.createElement('div');
            alertDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #117e8c;
                color: white;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                z-index: 3000;
                max-width: 400px;
                border: 1px solid rgba(255,255,255,0.2);
                animation: slideInRight 0.5s ease;
            `;
            
            alertDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span style="white-space: pre-line;">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: auto;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    });
</script>
</body>
</html>
