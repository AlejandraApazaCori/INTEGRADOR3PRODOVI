document.addEventListener('DOMContentLoaded', function () {
    // Variables globales
    let hasLinkedAccounts = false;
    let socialAccounts = {};

    // Función para verificar si el usuario tiene cuentas vinculadas
    function checkSocialAccounts() {
        fetch('/api/social-accounts')
            .then(response => response.json())
            .then(data => {
                hasLinkedAccounts = data.has_linked_accounts;
                socialAccounts = data.social_accounts;

                // Actualizar el estado de los botones en el modal
                updateSocialAccountStatus();

                // CAMBIO CLAVE: Si no tiene cuentas vinculadas, mostramos el AVISO SUPERIOR, no el modal
                if (!hasLinkedAccounts) {
                    showAlert();
                }
            })
            .catch(error => {
                console.error('Error al verificar cuentas sociales:', error);
                // En caso de error, mostramos el aviso para que el usuario pueda decidir qué hacer
                showAlert();
            });
    }

    // Función para actualizar el estado de las cuentas sociales en el modal
    function updateSocialAccountStatus() {
        const providers = ['facebook', 'instagram', 'tiktok'];

        providers.forEach(provider => {
            const statusElement = document.querySelector(`.link-status[data-provider="${provider}"]`);
            const buttonElement = document.querySelector(`.social-link-btn[data-provider="${provider}"]`);

            if (statusElement && buttonElement) {
                const isLinked = socialAccounts.some(account => account.provider === provider);

                if (isLinked) {
                    statusElement.innerHTML = `
                        <span class="text-sm text-green-600 font-medium">Vinculado</span>
                        <svg class="w-4 h-4 text-green-600 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    `;
                    buttonElement.classList.add('opacity-75');
                    buttonElement.querySelector('span').textContent = `${provider.charAt(0).toUpperCase() + provider.slice(1)} (vinculado)`;
                } else {
                    statusElement.innerHTML = `<span class="text-sm text-gray-500">No vinculado</span>`;
                    buttonElement.classList.remove('opacity-75');
                    buttonElement.querySelector('span').textContent = provider.charAt(0).toUpperCase() + provider.slice(1);
                }
            }
        });
    }

    // Función para mostrar el modal de vinculación
    function showSocialModal() {
        const modal = document.getElementById('link-social-modal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Función para ocultar el modal de vinculación
    function hideSocialModal() {
        const modal = document.getElementById('link-social-modal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Función para ocultar el modal y redirigir
    function hideSocialModalAndRedirect() {
        hideSocialModal();
        
        // Redirigir según si el usuario tiene empresas o no
        if (window.userHasCompanies) {
            window.location.href = '/clientes/dashboard';
        } else {
            window.location.href = '/empresas/create';
        }
    }

    // Función para mostrar el aviso superior
    function showAlert() {
        const alert = document.getElementById('social-accounts-alert');
        const dashboardContent = document.getElementById('dashboard-content');

        alert.classList.remove('hidden');
        dashboardContent.style.paddingTop = '80px'; // Ajustar el padding para el aviso
    }

    // Función para ocultar el aviso superior
    function hideAlert() {
        const alert = document.getElementById('social-accounts-alert');
        const dashboardContent = document.getElementById('dashboard-content');

        alert.classList.add('hidden');
        dashboardContent.style.paddingTop = '0';
    }

    // Event listeners para el modal de vinculación
    document.getElementById('close-modal-btn').addEventListener('click', hideSocialModalAndRedirect);

    // Event listeners para el aviso superior
    document.getElementById('link-now-btn').addEventListener('click', function () {
        hideAlert();
        showSocialModal();
    });

    document.getElementById('dismiss-alert-btn').addEventListener('click', hideSocialModalAndRedirect);

    // Event listener para el botón "No tengo cuentas"
    document.getElementById('no-accounts-btn').addEventListener('click', function () {
        // Simulación: creamos cuentas falsas para el usuario
        const providers = ['facebook', 'instagram', 'tiktok'];
        let promises = [];

        providers.forEach(provider => {
            promises.push(
                fetch('/api/social-accounts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ provider: provider })
                })
            );
        });

        Promise.all(promises)
            .then(responses => Promise.all(responses.map(res => res.json())))
            .then(data => {
                // Actualizar el estado
                hasLinkedAccounts = true;
                updateSocialAccountStatus();
                hideSocialModal();

                // Mostrar mensaje de éxito
                showNotification('¡Perfecto! Hemos creado tus cuentas de redes sociales.', 'success');
                
                // Redirigir después de un breve retraso
                setTimeout(() => {
                    if (window.userHasCompanies) {
                        window.location.href = '/clientes/dashboard';
                    } else {
                        window.location.href = '/empresas/create';
                    }
                }, 2000);
            })
            .catch(error => {
                console.error('Error al crear cuentas:', error);
                showNotification('Hubo un error al crear tus cuentas. Por favor, intenta nuevamente.', 'error');
            });
    });

    // Event listeners para los botones de vinculación de redes sociales
    document.querySelectorAll('.social-link-btn').forEach(button => {
        button.addEventListener('click', function () {
            const provider = this.getAttribute('data-provider');

            // Verificar si ya está vinculado
            const isLinked = socialAccounts.some(account => account.provider === provider);

            if (isLinked) {
                // Si ya está vinculado, mostramos un mensaje
                showNotification(`Tu cuenta de ${provider} ya está vinculada.`, 'info');
                return;
            }

            // Simulación de vinculación
            fetch('/api/social-accounts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ provider: provider })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar el estado
                        socialAccounts.push(data.account);
                        hasLinkedAccounts = socialAccounts.length > 0;
                        updateSocialAccountStatus();

                        showNotification(data.message, 'success');

                        // Si ya tiene al menos una cuenta vinculada, ocultamos el aviso
                        if (hasLinkedAccounts) {
                            hideAlert();
                        }
                    } else {
                        showNotification('Error al vincular la cuenta. Por favor, intenta nuevamente.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error al vincular cuenta:', error);
                    showNotification('Error al vincular la cuenta. Por favor, intenta nuevamente.', 'error');
                });
        });
    });

    // Función para mostrar notificaciones
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
        notification.innerHTML = `
            <div class="flex items-center">
                <p>${message}</p>
                <button class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Animación de entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Event listener para cerrar
        notification.querySelector('button').addEventListener('click', function () {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        });

        // Cerrar automáticamente después de 5 segundos
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }
        }, 5000);
    }

    // Función para cargar el plan contratado (código original)
    function fetchPlanContratado() {
        fetch('/cliente/plan-contratado')
            .then(response => {
                if (!response.ok) {
                    throw new Error('No se pudo obtener la información del plan');
                }
                return response.json();
            })
            .then(data => {
                renderPlanContratado(data.plan);
                setupPlanModal(data.plan);
            })
            .catch(error => {
                console.error('Error:', error);
                renderErrorPlanContratado(error.message);
            });
    }
    
    // Resto del código original del dashboard...
    function renderPlanContratado(plan) {
        const container = document.getElementById('plan-contratado-container');
        
        // Crear características HTML con diseño moderno
        let caracteristicasHtml = '';
        if (plan.caracteristicas && plan.caracteristicas.length > 0) {
            plan.caracteristicas.forEach((caracteristica, index) => {
                const colors = [
                    'from-blue-500 to-indigo-600',
                    'from-purple-500 to-pink-600', 
                    'from-green-500 to-emerald-600',
                    'from-orange-500 to-red-600',
                    'from-cyan-500 to-blue-600'
                ];
                const colorClass = colors[index % colors.length];
                
                caracteristicasHtml += `
                    <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br ${colorClass} opacity-10 rounded-full -translate-y-4 translate-x-4 group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-gradient-to-br ${colorClass} rounded-xl shadow-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <h4 class="font-semibold text-gray-700 text-sm mb-2">${caracteristica.nombre}</h4>
                            <p class="text-3xl font-bold bg-gradient-to-r ${colorClass} bg-clip-text text-transparent">
                                ${caracteristica.cantidad}${caracteristica.unidad || ''}
                            </p>
                        </div>
                    </div>
                `;
            });
        }
        
        const statusColors = {
            'activa': 'bg-green-100 text-green-800 border-green-200',
            'pendiente': 'bg-amber-100 text-amber-800 border-amber-200',
            'inactiva': 'bg-red-100 text-red-800 border-red-200'
        };
        
        container.innerHTML = `
            <div class="p-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-8">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">${plan.nombre}</h3>
                                <p class="text-gray-600">${plan.descripcion || 'Plan de marketing digital'}</p>
                            </div>
                        </div>
                        
                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600">Ciclo actual:</span>
                                    <span class="text-sm font-semibold text-gray-800">${plan.fecha_inicio} - ${plan.fecha_fin}</span>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border ${statusColors[plan.estado] || statusColors['inactiva']}">
                                    <div class="w-2 h-2 rounded-full bg-current mr-2"></div>
                                    ${plan.estado}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 lg:mt-0 lg:ml-8">
                        <button id="ver-detalles-btn" class="group relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl">
                            <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <div class="relative flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Ver detalles</span>
                            </div>
                        </button>
                    </div>
                </div>
                
 
            </div>
        `;
    }
    
    function setupPlanModal(plan) {
        const modal = document.getElementById('plan-modal');
        const verDetallesBtn = document.getElementById('ver-detalles-btn');
        const closeModalBtn = document.getElementById('close-modal');
        const closeModalFooterBtn = document.getElementById('close-modal-footer');
        
        // Llenar datos del modal
        document.getElementById('modal-plan-title').textContent = `Plan ${plan.nombre}`;
        document.getElementById('modal-plan-dates').textContent = `${plan.fecha_inicio} - ${plan.fecha_fin}`;
        
        const statusColors = {
            'activa': 'text-green-600',
            'pendiente': 'text-amber-600',
            'inactiva': 'text-red-600'
        };
        
        document.getElementById('modal-plan-status').innerHTML = `
            Estado: <span class="${statusColors[plan.estado] || statusColors['inactiva']} font-semibold">${plan.estado}</span>
        `;
        document.getElementById('modal-plan-description').textContent = plan.descripcion || 'No hay descripción disponible';
        
        // Llenar características con diseño moderno
        const featuresList = document.getElementById('modal-plan-features');
        featuresList.innerHTML = '';
        
        if (plan.todas_caracteristicas && plan.todas_caracteristicas.length > 0) {
            plan.todas_caracteristicas.forEach(caracteristica => {
                const div = document.createElement('div');
                div.className = 'flex items-center p-3 bg-gray-50 rounded-xl border border-gray-200';
                
                const starIcon = caracteristica.es_destacado ? 
                    '<svg class="w-5 h-5 text-amber-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>' : 
                    '<svg class="w-5 h-5 text-indigo-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                
                div.innerHTML = `
                    ${starIcon}
                    <div class="flex-1">
                        <span class="font-medium text-gray-800">${caracteristica.nombre}</span>
                        <span class="text-gray-600">: ${caracteristica.cantidad}${caracteristica.unidad || ''}</span>
                        ${caracteristica.frecuencia ? `<span class="text-sm text-gray-500 block">${caracteristica.frecuencia}</span>` : ''}
                    </div>
                `;
                featuresList.appendChild(div);
            });
        } else {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-center p-6 text-gray-500';
            div.innerHTML = `
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                No se encontraron características
            `;
            featuresList.appendChild(div);
        }
        
        // Event listeners
        function openModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        verDetallesBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);
        closeModalFooterBtn.addEventListener('click', closeModal);
        
        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
        
        // Cerrar al hacer clic fuera del modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }  
        
    function renderErrorPlanContratado(message) {
        const container = document.getElementById('plan-contratado-container');
        container.innerHTML = `
            <div class="text-center py-16">
                <div class="relative">
                    <div class="w-20 h-20 bg-red-100 rounded-2xl mx-auto flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Error al cargar el plan</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <button onclick="fetchPlanContratado()" class="group relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <div class="relative flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Reintentar</span>
                    </div>
                </button>
            </div>
        `;
    }
    
    // Hacer la función accesible globalmente para el botón de reintento
    window.fetchPlanContratado = fetchPlanContratado;

    // Inicializar la aplicación
    checkSocialAccounts();
    fetchPlanContratado();
});