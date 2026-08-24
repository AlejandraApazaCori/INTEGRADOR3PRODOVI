@extends('layouts.app')

@section('title', 'Gestionar Cuestionario')

@section('content')
<div id="questionnaire-index" class="min-h-screen bg-white">
    <div class="w-full">
        <!-- Banner con fondo geométrico -->
        <div class="overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8 flex items-center justify-between gap-8">
                <div class="hero-content">
                    <a href="{{ route('administrador.dashboard') }}" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Volver al dashboard
                    </a>
                    <h1 class="hero-title">Estructura del <span>cuestionario</span></h1>
                    <p class="hero-description">Organiza los temas y preguntas de tu cuestionario.</p>
                </div>
                <a href="{{ route('administrador.cuestionario.estructura.create') }}" class="banner-new-topic-button">
                    <i class="fas fa-plus-circle"></i>Añadir nuevo tema
                </a>
            </div>
        </div>

        <section class="questionnaire-overview">
            <div>
                <span class="questionnaire-overview-kicker"><i class="fas fa-sitemap"></i> Constructor del cuestionario</span>
                <h2>Temas y preguntas</h2>
                <p>Define el recorrido que completarán tus clientes y reorganiza cada sección arrastrando sus cards.</p>
            </div>
        </section>

        <!-- Alertas mejoradas -->
        @if(session('success'))
            <div class="status-alert mb-6 border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        @php
            $totalQuestions = $temas->sum(fn ($tema) => $tema->preguntas->count());
            $requiredQuestions = $temas->sum(fn ($tema) => $tema->preguntas->where('requerido', true)->count());
            $optionalQuestions = max(0, $totalQuestions - $requiredQuestions);
        @endphp
        <section class="questionnaire-kpis" aria-label="Resumen del cuestionario">
            <article class="questionnaire-kpi-total"><div><span>Total de temas</span><strong>{{ $temas->count() }}</strong><small>Secciones del cuestionario</small></div><i class="fas fa-layer-group"></i></article>
            <article class="questionnaire-kpi-questions"><div><span>Total de preguntas</span><strong>{{ $totalQuestions }}</strong><small>Preguntas configuradas</small></div><i class="fas fa-circle-question"></i></article>
            <article class="questionnaire-kpi-required"><div><span>Obligatorias</span><strong>{{ $requiredQuestions }}</strong><small>Requieren una respuesta</small></div><i class="fas fa-circle-check"></i></article>
            <article class="questionnaire-kpi-optional"><div><span>Opcionales</span><strong>{{ $optionalQuestions }}</strong><small>El cliente puede omitirlas</small></div><i class="fas fa-message"></i></article>
        </section>

        <!-- Lista de temas mejorada -->
        <div class="topics-panel bg-white overflow-hidden">
            <div class="topics-toolbar px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">
                    <i class="fas fa-arrows-alt mr-2 text-gray-400"></i>
                    Arrastra los temas para reordenarlos
                </span>
                <span class="text-sm text-gray-500" id="temas-count">
                    <i class="fas fa-list mr-1"></i>
                    {{ $temas->count() }} temas
                </span>
            </div>
            
            <ul class="divide-y divide-gray-100" id="temas-list">
                @forelse($temas as $index => $tema)
                    <li class="topic-row px-6 py-4 flex items-center justify-between transition-colors duration-150 group" data-id="{{ $tema->id }}" tabindex="0" role="button" aria-label="Vista previa de {{ $tema->nombre_tema }}">
                        <div class="flex items-center flex-1">
                            <div class="flex items-center cursor-move mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-grip-vertical text-lg"></i>
                            </div>
                            <div class="flex items-center gap-4 flex-1">
                                <div class="topic-number flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-600 font-semibold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">
                                        {{ $tema->nombre_tema }}
                                    </p>
                                    <p class="topic-description">{{ $tema->descripcion_tema ?: 'Sin descripción adicional para este tema.' }}</p>
                                    <div class="flex items-center mt-1 gap-4">
                                        <span class="inline-flex items-center text-sm text-gray-500">
                                            <i class="fas fa-question-circle mr-1.5 text-gray-400"></i>
                                            {{ $tema->preguntas->count() }} pregunta(s)
                                        </span>
                                        <span class="topic-id text-xs px-2 py-1 bg-gray-100 text-gray-600">
                                            <i class="fas fa-hashtag mr-1 text-gray-400"></i>
                                            ID: {{ $tema->id }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('administrador.cuestionario.estructura.edit', $tema->id) }}" 
                               class="topic-action p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 transition-all duration-200">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('administrador.cuestionario.estructura.destroy', $tema->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este tema y todas sus preguntas?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="topic-action p-2 text-red-500 hover:text-red-700 hover:bg-red-50 transition-all duration-200">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-layer-group text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 font-medium mb-2">No hay temas creados</p>
                            <p class="text-gray-400 text-sm mb-4">Comienza creando el primer tema de tu cuestionario</p>
                            <a href="{{ route('administrador.cuestionario.estructura.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Crear primer tema
                            </a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Footer informativo -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p><i class="fas fa-lightbulb mr-2 text-amber-400"></i> Los temas se muestran en el orden que aparecen aquí. Arrástralos para reordenarlos.</p>
        </div>
    </div>
</div>

<!-- Script para arrastrar y soltar (reordenar) -->
<div id="topic-preview-modal" class="preview-modal hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="preview-topic-title">
    <div class="preview-backdrop" data-close-preview></div>
    <div class="preview-dialog">
        <header class="preview-header">
            <div>
                <span class="preview-kicker">Vista previa del cliente</span>
                <div id="preview-timeline" class="preview-timeline" aria-label="Progreso de preguntas"></div>
                <h2 id="preview-topic-title" class="preview-accessible-title"></h2>
                <p id="preview-topic-description" class="preview-accessible-title"></p>
            </div>
            <button type="button" class="preview-close" data-close-preview aria-label="Cerrar vista previa">
                <i class="fas fa-xmark"></i>
            </button>
        </header>
        <div class="preview-side-art preview-side-left" aria-hidden="true">
            <span class="preview-shape shape-01"></span><span class="preview-shape shape-02"></span><span class="preview-shape shape-03"></span><span class="preview-shape shape-04"></span><span class="preview-shape shape-05"></span>
        </div>
        <div class="preview-side-art preview-side-right" aria-hidden="true">
            <span class="preview-shape shape-05"></span><span class="preview-shape shape-04"></span><span class="preview-shape shape-03"></span><span class="preview-shape shape-02"></span><span class="preview-shape shape-01"></span>
        </div>
        <div id="preview-questions" class="preview-body"></div>
        <footer class="preview-footer">
            <button type="button" id="preview-prev" class="preview-nav-button preview-prev">
                <i class="fas fa-arrow-left"></i> Anterior
            </button>
            <span id="preview-progress" class="preview-progress"></span>
            <button type="button" id="preview-next" class="preview-nav-button preview-next">
                Siguiente <i class="fas fa-arrow-right"></i>
            </button>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('temas-list');
        const temasCount = document.getElementById('temas-count');
        const previewModal = document.getElementById('topic-preview-modal');
        const previewTitle = document.getElementById('preview-topic-title');
        const previewDescription = document.getElementById('preview-topic-description');
        const previewQuestions = document.getElementById('preview-questions');
        const previewTimeline = document.getElementById('preview-timeline');
        const previewPrev = document.getElementById('preview-prev');
        const previewNext = document.getElementById('preview-next');
        const previewProgress = document.getElementById('preview-progress');
        const previewTopics = Object.fromEntries(@json($temas).map(tema => [String(tema.id), tema]));
        let previewStep = -1;

        const escapeHtml = value => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        function questionControl(question, index) {
            const options = Array.isArray(question.opciones) ? question.opciones : [];
            const fieldId = `preview-question-${index}`;

            if (question.tipo_respuesta === 'texto_largo') {
                return `<textarea id="${fieldId}" class="preview-auto-grow" rows="4" placeholder="Escribe tu respuesta..."></textarea>`;
            }

            if (question.tipo_respuesta === 'opcion_multiple') {
                return `<div class="preview-custom-select" id="${fieldId}">
                    <button type="button" class="preview-select-trigger" aria-expanded="false">
                        <span>Selecciona una opción</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="preview-select-menu">
                        ${options.map((option, optionIndex) => `<label>
                            <input type="radio" name="${fieldId}" value="${escapeHtml(option)}">
                            <span>${escapeHtml(option)}</span>
                            <i class="fas fa-check"></i>
                        </label>`).join('')}
                    </div>
                </div>`;
            }

            if (question.tipo_respuesta === 'checkbox') {
                return `<div class="preview-checkboxes">
                    ${options.map((option, optionIndex) => `<label for="${fieldId}-${optionIndex}">
                        <input id="${fieldId}-${optionIndex}" type="checkbox" value="${escapeHtml(option)}">
                        <span class="preview-checkbox-mark"><i class="fas fa-check"></i></span>
                        <span>${escapeHtml(option)}</span>
                    </label>`).join('')}
                </div>`;
            }

            return `<textarea id="${fieldId}" class="preview-auto-grow preview-short-answer" rows="1" placeholder="Escribe tu respuesta..."></textarea>`;
        }

        function openTopicPreview(topicId) {
            const topic = previewTopics[String(topicId)];
            if (!topic) return;

            previewTitle.textContent = topic.nombre_tema;
            previewDescription.textContent = topic.descripcion_tema || 'Completa las siguientes preguntas para continuar.';
            const questions = Array.isArray(topic.preguntas) ? topic.preguntas : [];
            previewTimeline.innerHTML = questions.map((question, index) => `<span class="preview-timeline-step" data-timeline-step="${index}">${index + 1}</span>`).join('');
            previewQuestions.innerHTML = `<section class="preview-cover" data-preview-cover>
                    <span class="preview-cover-label">Sección del cuestionario</span>
                    <h3>${escapeHtml(topic.nombre_tema)}</h3>
                    <p>${escapeHtml(topic.descripcion_tema || 'Completa las siguientes preguntas para continuar.')}</p>
                    <div class="preview-cover-meta"><i class="fas fa-list-check"></i> ${questions.length} pregunta${questions.length === 1 ? '' : 's'}</div>
                </section>` + (questions.length
                ? questions.map((question, index) => `<div class="preview-question hidden" data-preview-step="${index}">
                    <div class="preview-question-number">${index + 1}</div>
                    <div class="preview-question-content">
                        <label for="preview-question-${index}">${escapeHtml(question.pregunta)}${question.requerido ? '<span class="preview-required">*</span>' : '<span class="preview-optional">(Opcional)</span>'}</label>
                        ${question.ayuda ? `<p>${escapeHtml(question.ayuda)}</p>` : ''}
                        ${questionControl(question, index)}
                    </div>
                </div>`).join('')
                : '');

            previewStep = -1;
            setupCustomDropdowns();
            setupAutoGrowFields();
            updatePreviewStep();

            previewModal.classList.remove('hidden');
            previewModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            previewModal.querySelector('.preview-close').focus();
        }

        function setupCustomDropdowns() {
            previewQuestions.querySelectorAll('.preview-custom-select').forEach(dropdown => {
                const trigger = dropdown.querySelector('.preview-select-trigger');
                const current = trigger.querySelector('span');
                trigger.addEventListener('click', () => {
                    const willOpen = !dropdown.classList.contains('is-open');
                    previewQuestions.querySelectorAll('.preview-custom-select.is-open').forEach(item => {
                        item.classList.remove('is-open');
                        item.querySelector('.preview-select-trigger').setAttribute('aria-expanded', 'false');
                    });
                    dropdown.classList.toggle('is-open', willOpen);
                    trigger.setAttribute('aria-expanded', String(willOpen));
                });
                dropdown.querySelectorAll('input[type="radio"]').forEach(option => {
                    option.addEventListener('change', () => {
                        current.textContent = option.value;
                        dropdown.querySelectorAll('label').forEach(label => label.classList.toggle('is-selected', label.contains(option)));
                        dropdown.classList.remove('is-open');
                        trigger.setAttribute('aria-expanded', 'false');
                    });
                });
            });
        }

        function setupAutoGrowFields() {
            previewQuestions.querySelectorAll('.preview-auto-grow').forEach(field => {
                const resizeField = () => {
                    field.style.height = 'auto';
                    field.style.height = `${field.scrollHeight}px`;
                };
                field.addEventListener('input', resizeField);
                resizeField();
            });
        }

        function updatePreviewStep() {
            const steps = Array.from(previewQuestions.querySelectorAll('[data-preview-step]'));
            const cover = previewQuestions.querySelector('[data-preview-cover]');
            cover?.classList.toggle('hidden', previewStep !== -1);
            previewModal.classList.toggle('is-cover', previewStep === -1);
            steps.forEach((step, index) => step.classList.toggle('hidden', index !== previewStep));
            previewQuestions.scrollTop = 0;
            previewTimeline.querySelectorAll('[data-timeline-step]').forEach((item, index) => {
                item.classList.toggle('is-active', index === previewStep);
                item.classList.toggle('is-complete', previewStep > index);
            });
            previewPrev.disabled = previewStep === -1;
            previewPrev.classList.toggle('is-hidden', previewStep === -1);
            previewNext.disabled = false;
            previewNext.innerHTML = previewStep === -1 && steps.length === 0
                ? 'Cerrar <i class="fas fa-xmark"></i>'
                : previewStep === -1
                ? 'Comenzar <i class="fas fa-arrow-right"></i>'
                : previewStep === steps.length - 1
                ? 'Finalizar vista previa <i class="fas fa-check"></i>'
                : 'Siguiente <i class="fas fa-arrow-right"></i>';
            previewProgress.textContent = previewStep === -1 ? '' : `Pregunta ${previewStep + 1} de ${steps.length}`;
        }

        function closeTopicPreview() {
            previewModal.classList.add('hidden');
            previewModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.topic-row').forEach(row => {
            row.addEventListener('click', event => {
                if (event.target.closest('a, button, form, .cursor-move')) return;
                openTopicPreview(row.dataset.id);
            });
            row.addEventListener('keydown', event => {
                if (event.target.closest('a, button, form, .cursor-move')) return;
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openTopicPreview(row.dataset.id);
                }
            });
        });

        previewModal.querySelectorAll('[data-close-preview]').forEach(control => control.addEventListener('click', closeTopicPreview));
        previewPrev.addEventListener('click', () => {
            if (previewStep >= 0) {
                previewStep--;
                updatePreviewStep();
            }
        });
        previewNext.addEventListener('click', () => {
            const steps = previewQuestions.querySelectorAll('[data-preview-step]');
            if (previewStep === -1 && steps.length) {
                previewStep = 0;
                updatePreviewStep();
                return;
            }
            if (previewStep < steps.length - 1) {
                previewStep++;
                updatePreviewStep();
            } else {
                closeTopicPreview();
            }
        });
        previewModal.addEventListener('click', event => {
            if (!event.target.closest('.preview-custom-select')) {
                previewQuestions.querySelectorAll('.preview-custom-select.is-open').forEach(dropdown => {
                    dropdown.classList.remove('is-open');
                    dropdown.querySelector('.preview-select-trigger').setAttribute('aria-expanded', 'false');
                });
            }
        });
        document.body.appendChild(previewModal);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !previewModal.classList.contains('hidden')) closeTopicPreview();
        });
        
        if (el) {
            new Sortable(el, {
                animation: 200,
                handle: '.cursor-move',
                ghostClass: 'bg-indigo-50',
                dragClass: 'shadow-lg',
                onEnd: function (evt) {
                    const temas = [...el.children].map(li => li.dataset.id);
                    
                    // Mostrar indicador de carga
                    const items = el.children;
                    for (let item of items) {
                        item.style.opacity = '0.5';
                    }
                    
                    fetch('{{ route("administrador.cuestionario.estructura.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ temas: temas })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Orden actualizado:', data);
                        // Restaurar opacidad
                        for (let item of items) {
                            item.style.opacity = '1';
                        }
                        // Actualizar numeración
                        if (temasCount) {
                            temasCount.textContent = `${temas.length} temas`;
                        }
                        // Mostrar notificación de éxito
                        showNotification('Orden actualizado correctamente', 'success');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Restaurar opacidad
                        for (let item of items) {
                            item.style.opacity = '1';
                        }
                        showNotification('Error al actualizar el orden', 'error');
                    });
                }
            });
        }
        
        // Función para mostrar notificaciones
        function showNotification(message, type = 'success') {
            const colors = {
                success: 'bg-green-50 border-green-500 text-green-700',
                error: 'bg-red-50 border-red-500 text-red-700'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed bottom-6 right-6 p-4 border-l-4 rounded-r-xl shadow-lg ${colors[type]} z-50 transform transition-all duration-500 translate-x-full`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-3 text-${type === 'success' ? 'green' : 'red'}-500"></i>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Animar entrada
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
    });
</script>

<style>
    /* Banner geométrico - Mismo estilo que las otras vistas */
    .rp-banner {
        background:
            linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(315deg, #4f46e5 25%, transparent 25%),
            linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
            linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
        background-size:
            100px 100px,
            100px 100px,
            100px 100px,
            100px 100px,
            100% 100%;
        background-color: #1d4ed8;
        position: relative;
    }

    .rp-banner-overlay {
        background:
            radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
        background-size:     50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat:   no-repeat;
    }

    /* Diseño plano compartido con el constructor */
    #questionnaire-index {
        --prodovi-purple: #5B2B76;
        --prodovi-orange: #EF6C22;
        --prodovi-turquoise: #117E8C;
        color: #17131d;
    }
    #questionnaire-index > .w-full > :not(.rp-banner) {
        margin-left: 2rem;
        margin-right: 2rem;
    }
    #questionnaire-index .rp-banner {
        min-height: 150px;
        border-bottom: 5px solid var(--prodovi-orange);
        border-radius: 0;
        background: #242426;
    }
    #questionnaire-index .rp-banner-overlay {
        display: none;
    }
    #questionnaire-index .hero-content { position: relative; z-index: 2; max-width: 720px; }
    #questionnaire-index .hero-title { margin: 0; color: #fff; font-size: clamp(1.65rem, 3vw, 2.35rem); font-weight: 800; line-height: 1.08; letter-spacing: -.035em; }
    #questionnaire-index .hero-title span { color: var(--prodovi-orange); }
    #questionnaire-index .hero-description { max-width: 620px; margin-top: 11px; color: #96969c; font-size: .86rem; line-height: 1.55; }
    #questionnaire-index .back-button { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 13px; padding: 9px 14px; border: 1px solid rgba(255,255,255,.22); border-radius: 3px; background: rgba(255,255,255,.1); color: #fff; font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; transition: .2s ease; }
    #questionnaire-index .back-button:hover { border-color: var(--prodovi-orange); background: var(--prodovi-orange); transform: translateX(-2px); }
    #questionnaire-index .login-mosaic { position: relative; z-index: 2; width: 144px; height: 96px; flex: 0 0 auto; display: grid; grid-template-columns: repeat(3,1fr); grid-template-rows: repeat(2,1fr); }
    #questionnaire-index .login-mosaic span { display: block; }
    #questionnaire-index .login-mosaic span:nth-child(1) { background: var(--prodovi-orange); border-radius: 100% 0 0 0; }
    #questionnaire-index .login-mosaic span:nth-child(2) { background: #F5A900; border-radius: 0 0 0 100%; }
    #questionnaire-index .login-mosaic span:nth-child(3) { background: var(--prodovi-purple); border-radius: 100% 0 100% 0; }
    #questionnaire-index .login-mosaic span:nth-child(4) { background: var(--prodovi-turquoise); border-radius: 0 100% 0 100%; }
    #questionnaire-index .login-mosaic span:nth-child(5) { background: #7DA533; border-radius: 50%; }
    #questionnaire-index .login-mosaic span:nth-child(6) { border: 12px solid #607078; border-top-color: transparent; border-left-color: transparent; border-radius: 50%; transform: rotate(45deg); }
    #questionnaire-index .page-actions { margin-top: 2rem; }
    #questionnaire-index .new-topic-button {
        border-radius: 3px;
        background: linear-gradient(135deg, var(--prodovi-orange), #dc5710);
        box-shadow: none;
    }
    #questionnaire-index .new-topic-button:hover { background: #ff7d32; }
    #questionnaire-index .status-alert {
        margin-top: 2rem;
        border-radius: 3px;
    }
    #questionnaire-index .topics-panel {
        margin-top: 2rem;
        border-top: 1px solid #d9d2dc;
        border-bottom: 1px solid #d9d2dc;
        border-radius: 0;
        box-shadow: none;
    }
    #questionnaire-index .topics-toolbar {
        border-left: 4px solid var(--prodovi-purple);
        background: #f7f5f8;
    }
    #questionnaire-index .topic-row {
        border-left: 4px solid transparent;
        background: #fff;
    }
    #questionnaire-index .topic-row:hover {
        border-left-color: var(--prodovi-orange);
        background: #faf8fb;
    }
    #questionnaire-index .topic-number,
    #questionnaire-index .topic-id,
    #questionnaire-index .topic-action { border-radius: 2px; }
    #questionnaire-index .topic-number {
        background: var(--prodovi-purple);
        color: #fff;
    }
    #questionnaire-index #temas-list > li:last-child a,
    #questionnaire-index #temas-list > li:last-child button { border-radius: 3px; }
    #questionnaire-index .topic-row { cursor: pointer; }
    #questionnaire-index .topic-row:focus-visible {
        outline: 3px solid rgba(91,43,118,.2);
        outline-offset: -3px;
    }

    /* Nueva superficie administrativa. La vista previa modal se conserva sin cambios. */
    #questionnaire-index{min-height:100vh!important;padding-bottom:44px;background:#f7f8fa!important;--questionnaire-orange:#ef6c22;--questionnaire-turquoise:#117e8c;--questionnaire-green:#7da533;--questionnaire-purple:#5b2b76;--questionnaire-ink:#302834}#questionnaire-index .rp-banner{min-height:180px!important;border:0!important;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb)!important;background-color:#1d4ed8!important;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%!important}#questionnaire-index .rp-banner-overlay{display:block!important;background:linear-gradient(rgba(15,23,42,.2),rgba(15,23,42,.2)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 48%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.16),transparent 45%)!important}#questionnaire-index .rp-banner>.relative{min-height:180px;padding:28px 48px!important}#questionnaire-index .hero-title span{color:#dbeafe}#questionnaire-index .hero-description{color:#dbeafe}#questionnaire-index .back-button{border-radius:9px;background:rgba(255,255,255,.12);text-transform:none}#questionnaire-index .back-button:hover{border-color:#fff;background:#fff;color:#2563eb}.questionnaire-overview{display:flex;align-items:center;justify-content:space-between;gap:28px;margin-top:24px!important;padding:20px 22px;border:1px solid #e1dde4;border-radius:15px;background:#fff;box-shadow:0 8px 22px rgba(48,40,52,.06)}.questionnaire-overview-kicker{display:flex;align-items:center;gap:7px;color:var(--questionnaire-turquoise);font-size:.62rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.questionnaire-overview h2{margin-top:5px;color:var(--questionnaire-ink);font-size:1.25rem;font-weight:900}.questionnaire-overview p{max-width:700px;margin-top:5px;color:#756a7a;font-size:.72rem;line-height:1.55}#questionnaire-index .new-topic-button{min-height:44px;display:inline-flex;align-items:center;justify-content:center;gap:8px;flex:none;padding:0 17px;border:0;border-radius:9px;background:var(--questionnaire-orange);color:#fff;font-size:.72rem;font-weight:900;box-shadow:0 8px 17px rgba(239,108,34,.18)}#questionnaire-index .new-topic-button:hover{background:#dc5710;transform:translateY(-1px)}#questionnaire-index .status-alert{margin-top:16px;margin-bottom:0!important;border-radius:10px}.questionnaire-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px!important}.questionnaire-kpis article{--kpi-color:#117e8c;display:flex;align-items:center;gap:14px;min-height:112px;padding:18px;border:1px solid #e1dde4;border-top:4px solid var(--kpi-color);border-radius:14px;background:#fff;box-shadow:0 8px 20px rgba(48,40,52,.06);transition:.2s}.questionnaire-kpis article:nth-child(2){--kpi-color:#5b2b76}.questionnaire-kpis article:nth-child(3){--kpi-color:#7da533}.questionnaire-kpis article:nth-child(4){--kpi-color:#ef6c22}.questionnaire-kpis article:hover{transform:translateY(-3px);box-shadow:0 13px 27px rgba(48,40,52,.1)}.questionnaire-kpis article>span{width:43px;height:43px;display:grid;place-items:center;flex:none;border-radius:12px;background:color-mix(in srgb,var(--kpi-color) 12%,white);color:var(--kpi-color)}.questionnaire-kpis small,.questionnaire-kpis strong,.questionnaire-kpis p{display:block}.questionnaire-kpis small{color:#706775;font-size:.61rem;font-weight:900;text-transform:uppercase}.questionnaire-kpis strong{margin-top:4px;color:var(--questionnaire-ink);font-size:1.55rem;font-weight:900;line-height:1}.questionnaire-kpis p{margin-top:5px;color:#938a96;font-size:.58rem}#questionnaire-index .topics-panel{overflow:visible;margin-top:16px!important;border:0;border-radius:0;background:transparent;box-shadow:none}#questionnaire-index .topics-toolbar{padding:13px 16px!important;border:1px solid #e1dde4!important;border-left:4px solid var(--questionnaire-turquoise)!important;border-radius:12px;background:#fff!important;box-shadow:0 5px 14px rgba(48,40,52,.05)}#questionnaire-index #temas-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:14px}#questionnaire-index .topic-row{position:relative;min-width:0;min-height:145px;padding:18px 17px!important;border:1px solid #ded9e1!important;border-top:4px solid var(--questionnaire-purple)!important;border-left:1px solid #ded9e1!important;border-radius:14px;background:#fff;box-shadow:0 9px 22px rgba(48,40,52,.07);cursor:pointer;transition:.2s}#questionnaire-index .topic-row:nth-child(3n+2){border-top-color:var(--questionnaire-orange)!important}#questionnaire-index .topic-row:nth-child(3n){border-top-color:var(--questionnaire-turquoise)!important}#questionnaire-index .topic-row:hover{border-left-color:#ded9e1!important;background:#fff;transform:translateY(-3px);box-shadow:0 14px 29px rgba(48,40,52,.11)}#questionnaire-index .topic-row>div:first-child{min-width:0;align-items:flex-start}#questionnaire-index .topic-row .cursor-move{width:28px;height:100%;align-self:stretch;justify-content:center;margin-right:12px!important;border-right:1px solid #ece8ee;color:#aaa1ad}#questionnaire-index .topic-row .cursor-move:hover{color:var(--questionnaire-turquoise)}#questionnaire-index .topic-number{width:36px;height:36px;flex:none;border-radius:10px;background:var(--questionnaire-purple);color:#fff}#questionnaire-index .topic-row:nth-child(3n+2) .topic-number{background:var(--questionnaire-orange)}#questionnaire-index .topic-row:nth-child(3n) .topic-number{background:var(--questionnaire-turquoise)}#questionnaire-index .topic-row p.text-base{overflow:hidden;color:var(--questionnaire-ink)!important;font-size:.82rem;text-overflow:ellipsis;white-space:nowrap}.topic-description{display:-webkit-box;max-width:420px;margin-top:4px;overflow:hidden;color:#817786;font-size:.65rem;line-height:1.45;-webkit-box-orient:vertical;-webkit-line-clamp:2}.topic-id{border-radius:999px!important;background:#f1eff2!important;color:#77707a!important}.topic-action{width:34px;height:34px;display:grid;place-items:center;border-radius:9px!important;background:#f4f1f5}.topic-action:hover{transform:translateY(-1px)}#questionnaire-index #temas-list>li:not(.topic-row){grid-column:1/-1;border:1px dashed #d4ced6;border-radius:14px;background:#fff}#questionnaire-index>.w-full>.mt-6{margin-top:18px!important;color:#817786}
    @media(max-width:1050px){.questionnaire-kpis{grid-template-columns:repeat(2,1fr)}#questionnaire-index #temas-list{grid-template-columns:1fr}}@media(max-width:640px){#questionnaire-index .rp-banner>.relative{padding:24px 18px!important}.questionnaire-overview{align-items:stretch;flex-direction:column}.questionnaire-overview .new-topic-button{width:100%}.questionnaire-kpis{grid-template-columns:1fr}.questionnaire-kpis article{min-height:96px}#questionnaire-index .topic-row{align-items:flex-start;flex-direction:column;gap:13px}#questionnaire-index .topic-row>div:last-child{align-self:flex-end}}

    /* Ajuste plano y cards de ancho completo, inspirado en el catálogo de planes. */
    #questionnaire-index .questionnaire-overview{margin-top:26px!important;padding:0 0 16px;border:0;border-bottom:1px solid #ddd8df;border-radius:0;background:transparent;box-shadow:none}#questionnaire-index .questionnaire-overview h2{font-size:1.35rem}#questionnaire-index .questionnaire-overview p{font-size:.74rem}#questionnaire-index .topics-panel{margin-top:22px!important}#questionnaire-index .topics-toolbar{padding:12px 2px 13px!important;border:0!important;border-bottom:1px solid #ddd8df!important;border-radius:0;background:transparent!important;box-shadow:none}#questionnaire-index .topics-toolbar>span:first-child{color:#655c68;font-weight:800}#questionnaire-index .topics-toolbar>span:first-child i{color:#117e8c}#questionnaire-index #temas-list{grid-template-columns:1fr!important;gap:18px;margin-top:18px}#questionnaire-index .topic-row{--topic-accent:#ef6c22;width:100%;min-height:156px;padding:0!important;overflow:hidden;border:1px solid #ded7e1!important;border-top:5px solid var(--topic-accent)!important;border-radius:5px;background:#fff;box-shadow:0 15px 36px #e5dfe7}#questionnaire-index .topic-row:nth-child(3n+2){--topic-accent:#117e8c}#questionnaire-index .topic-row:nth-child(3n){--topic-accent:#5b2b76}#questionnaire-index .topic-row:hover{border-color:#ded7e1!important;transform:translateY(-5px);box-shadow:0 22px 44px #d8d0db}#questionnaire-index .topic-row>div:first-child{width:100%;min-height:151px;padding:22px 24px}#questionnaire-index .topic-row .cursor-move{width:38px;height:38px;align-self:center;flex:none;margin-right:16px!important;border:0;border-radius:9px;background:#f1eff2;color:#8d8490}#questionnaire-index .topic-row .cursor-move:hover{background:#edf7f8;color:#117e8c}#questionnaire-index .topic-row .flex.items-center.gap-4.flex-1{min-width:0;width:100%}#questionnaire-index .topic-row .topic-number{width:42px;height:42px;border-radius:4px;background:var(--topic-accent)!important}#questionnaire-index .topic-row .topic-number+div{min-width:0;flex:1}#questionnaire-index .topic-row p.text-base{font-size:.95rem;font-weight:900}#questionnaire-index .topic-description{width:100%;max-width:none;margin-top:6px;font-size:.7rem;-webkit-line-clamp:2}#questionnaire-index .topic-row>div:last-child{align-self:stretch;display:flex;align-items:center;padding:0 22px;border-left:1px solid #e5dfe7;background:#f7f5f8}#questionnaire-index .topic-action{background:#fff;box-shadow:0 3px 8px rgba(48,40,52,.07)}#questionnaire-index .topic-id{background:#f4f1f5!important}#questionnaire-index>.w-full>.mt-6{margin-top:22px!important;padding-top:15px;border-top:1px solid #ddd8df}
    @media(max-width:700px){#questionnaire-index .topic-row{align-items:stretch;flex-direction:column}#questionnaire-index .topic-row>div:first-child{min-height:0;padding:18px 16px}#questionnaire-index .topic-row .cursor-move{margin-right:10px!important}#questionnaire-index .topic-row .flex.items-center.gap-4.flex-1{align-items:flex-start}#questionnaire-index .topic-row>div:last-child{align-self:stretch;justify-content:flex-end;padding:12px 16px;border-top:1px solid #e5dfe7;border-left:0}}

    #questionnaire-index .banner-new-topic-button{display:inline-flex;align-items:center;justify-content:center;gap:9px;flex:none;min-height:44px;padding:0 16px;border:1px solid #fff;border-radius:10px;background:#fff;color:#2563eb;font-size:.72rem;font-weight:900;text-decoration:none;box-shadow:0 9px 22px rgba(15,23,42,.16);transition:.18s}#questionnaire-index .banner-new-topic-button:hover{transform:translateY(-2px);box-shadow:0 13px 27px rgba(15,23,42,.22)}#questionnaire-index .questionnaire-overview{justify-content:flex-start}.questionnaire-kpis article{--kpi-accent:#117e8c;--kpi-soft:#e6f4f5;--kpi-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:132px;padding:21px;border:1px solid rgba(var(--kpi-rgb),.22);border-top:1px solid rgba(var(--kpi-rgb),.22);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--kpi-soft));box-shadow:inset 0 4px 0 var(--kpi-accent),0 10px 24px rgba(45,66,34,.09);transition:.22s}.questionnaire-kpis article::before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--kpi-rgb),.09);border-radius:50%}.questionnaire-kpis article::after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--kpi-accent) 1.4px,transparent 1.6px);background-size:9px 9px;transform:rotate(-5deg)}.questionnaire-kpis article:hover{transform:translateY(-5px);box-shadow:inset 0 4px 0 var(--kpi-accent),0 17px 32px rgba(var(--kpi-rgb),.16)}.questionnaire-kpis article div span,.questionnaire-kpis article div small{display:block}.questionnaire-kpis article div span{color:#596170;font-size:.7rem;font-weight:900;text-transform:uppercase}.questionnaire-kpis article div strong{display:block;margin-top:9px;color:#263024;font-size:1.85rem;font-weight:900;line-height:1}.questionnaire-kpis article div small{margin-top:8px;color:#7f8878;font-size:.62rem}.questionnaire-kpis article>i{width:52px;height:52px;display:grid;place-items:center;flex:none;border-radius:14px;background:var(--kpi-accent);color:#fff;font-size:1.18rem;box-shadow:0 8px 17px rgba(var(--kpi-rgb),.27)}.questionnaire-kpi-total{--kpi-accent:#117e8c!important;--kpi-soft:#e6f4f5!important;--kpi-rgb:17,126,140!important}.questionnaire-kpi-questions{--kpi-accent:#5b2b76!important;--kpi-soft:#f3edf6!important;--kpi-rgb:91,43,118!important}.questionnaire-kpi-required{--kpi-accent:#7da533!important;--kpi-soft:#f0f6e7!important;--kpi-rgb:125,165,51!important}.questionnaire-kpi-optional{--kpi-accent:#e37225!important;--kpi-soft:#fff0e6!important;--kpi-rgb:227,114,37!important}
    @media(max-width:640px){#questionnaire-index .banner-new-topic-button{width:100%}#questionnaire-index .rp-banner>.relative{align-items:stretch;flex-direction:column}}

    .preview-modal {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .preview-modal.hidden { display: none; }
    .preview-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(18,14,20,.72);
        backdrop-filter: blur(5px);
    }
    .preview-dialog {
        position: relative;
        width: min(880px, 100%);
        max-height: calc(100vh - 48px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 3px;
        background: #fff;
        box-shadow: 0 28px 80px rgba(0,0,0,.32);
    }
    .preview-side-art {
        position: absolute;
        z-index: 4;
        top: 160px;
        bottom: 78px;
        display: none;
        align-content: center;
        gap: 8px;
        width: 54px;
        pointer-events: none;
    }
    .preview-modal.is-cover .preview-side-art { display: grid; }
    .preview-side-left { left: 18px; }
    .preview-side-right { right: 18px; }
    .preview-shape {
        width: 54px;
        height: 48px;
        display: block;
        border-radius: 7px;
        opacity: .92;
    }
    .preview-shape.shape-01 {
        background: #EF6C22;
        clip-path: polygon(0 0, 100% 0, 100% 68%, 68% 68%, 68% 100%, 0 100%);
    }
    .preview-shape.shape-02 {
        background: #F5A900;
        clip-path: polygon(0 0, 100% 0, 100% 100%, 24% 100%, 24% 76%, 0 76%);
    }
    .preview-shape.shape-03 {
        background: #5B2B76;
        clip-path: polygon(18% 0, 100% 0, 100% 82%, 82% 100%, 0 100%, 0 18%);
    }
    .preview-shape.shape-04 {
        background: #117E8C;
        clip-path: polygon(0 0, 74% 0, 74% 24%, 100% 24%, 100% 100%, 0 100%);
    }
    .preview-shape.shape-05 {
        background: #7DA533;
        clip-path: polygon(0 0, 100% 0, 100% 100%, 34% 100%, 34% 66%, 0 66%);
    }
    .preview-header {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        padding: 26px 30px;
        border-bottom: 5px solid #EF6C22;
        background: #242426;
        color: #fff;
    }
    .preview-header > div:first-child { min-width: 0; flex: 1; }
    .preview-kicker {
        display: block;
        margin-bottom: 8px;
        color: #EF6C22;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }
    .preview-accessible-title {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0,0,0,0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    .preview-timeline {
        display: flex;
        align-items: center;
        max-width: 100%;
        padding: 5px 2px 2px;
        overflow-x: auto;
        scrollbar-width: thin;
    }
    .preview-timeline-step {
        position: relative;
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        flex: 0 0 30px;
        margin-right: 34px;
        border: 1px solid #666269;
        border-radius: 50%;
        background: #343436;
        color: #aaa5ad;
        font-size: .7rem;
        font-weight: 900;
        transition: .25s ease;
    }
    .preview-timeline-step:last-child { margin-right: 0; }
    .preview-timeline-step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 30px;
        width: 34px;
        height: 2px;
        background: #555158;
        transition: .25s ease;
    }
    .preview-timeline-step.is-active {
        border-color: #EF6C22;
        background: #EF6C22;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(239,108,34,.16);
    }
    .preview-timeline-step.is-complete {
        border-color: #117E8C;
        background: #117E8C;
        color: #fff;
    }
    .preview-timeline-step.is-complete::after { background: #117E8C; }
    .preview-header h2 {
        margin: 0;
        font-size: clamp(1.35rem, 3vw, 1.9rem);
        font-weight: 800;
        line-height: 1.15;
    }
    .preview-header p { margin-top: 8px; color: #aaa5ad; font-size: .86rem; }
    .preview-close {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 2px;
        background: rgba(255,255,255,.08);
        color: #fff;
        transition: .2s ease;
    }
    .preview-close:hover { border-color: #EF6C22; background: #EF6C22; }
    .preview-body {
        overflow-y: auto;
        min-height: 330px;
        padding: 8px 30px;
        background: #fff;
    }
    .preview-cover {
        min-height: 314px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 48px 92px;
        text-align: center;
    }
    .preview-cover.hidden { display: none; }
    .preview-cover-label {
        margin-bottom: 14px;
        color: #EF6C22;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }
    .preview-cover h3 {
        max-width: 650px;
        margin: 0;
        color: #29222c;
        font-size: clamp(1.7rem, 4vw, 2.6rem);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -.035em;
    }
    .preview-cover p {
        max-width: 620px;
        margin-top: 16px;
        color: #756b7a;
        font-size: .95rem;
        line-height: 1.65;
    }
    .preview-cover-meta {
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #e5e0e7;
        color: #5B2B76;
        font-size: .78rem;
        font-weight: 800;
    }
    .preview-cover-meta i { margin-right: 7px; color: #117E8C; }
    .preview-question {
        display: grid;
        grid-template-columns: 34px minmax(0,1fr);
        gap: 16px;
        padding: 24px 0;
        border-bottom: 1px solid #e5e0e7;
    }
    .preview-question:last-child { border-bottom: 0; }
    .preview-question.hidden { display: none; }
    .preview-question-number {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 2px;
        background: #5B2B76;
        color: #fff;
        font-size: .78rem;
        font-weight: 900;
    }
    .preview-question-content > label {
        display: block;
        margin-bottom: 7px;
        color: #28222b;
        font-size: .95rem;
        font-weight: 800;
    }
    .preview-question-content > p { margin-bottom: 10px; color: #817786; font-size: .78rem; }
    .preview-required { margin-left: 4px; color: #dc4d3f; }
    .preview-optional { margin-left: 7px; color: #938799; font-size: .76rem; font-weight: 600; }
    .preview-question input[type="text"],
    .preview-question textarea,
    .preview-select-trigger {
        width: 100%;
        min-height: 46px;
        padding: 11px 14px;
        border: 1px solid #d8cfdc;
        border-radius: 3px;
        background: #fff;
        color: #342c38;
        font-size: .88rem;
        outline: none;
    }
    .preview-question input[type="text"]:focus,
    .preview-question textarea:focus,
    .preview-select-trigger:focus { border-color: #5B2B76; box-shadow: 0 0 0 3px rgba(91,43,118,.1); }
    .preview-question textarea { resize: vertical; }
    .preview-question .preview-auto-grow {
        overflow-y: hidden;
        resize: none;
    }
    .preview-question .preview-short-answer { min-height: 46px; }
    .preview-custom-select { position: relative; }
    .preview-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left;
        cursor: pointer;
    }
    .preview-select-trigger i { color: #5B2B76; transition: transform .2s ease; }
    .preview-custom-select.is-open .preview-select-trigger { border-color: #5B2B76; }
    .preview-custom-select.is-open .preview-select-trigger i { transform: rotate(180deg); }
    .preview-select-menu {
        position: absolute;
        z-index: 20;
        top: calc(100% + 5px);
        right: 0;
        left: 0;
        display: grid;
        gap: 4px;
        padding: 8px;
        border: 1px solid #d8cfdc;
        border-radius: 3px;
        background: #fff;
        box-shadow: 0 16px 35px rgba(44,30,50,.18);
    }
    .preview-custom-select:not(.is-open) .preview-select-menu { display: none; }
    .preview-select-menu label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        color: #514557;
        font-size: .85rem;
        cursor: pointer;
    }
    .preview-select-menu label:hover,
    .preview-select-menu label.is-selected { background: #f5eff7; color: #5B2B76; }
    .preview-select-menu input { position: absolute; opacity: 0; pointer-events: none; }
    .preview-select-menu label > i { margin-left: auto; color: #5B2B76; opacity: 0; }
    .preview-select-menu label.is-selected > i { opacity: 1; }
    .preview-checkboxes { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 8px; }
    .preview-checkboxes label {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 46px;
        padding: 10px 12px;
        border: 1px solid #d8cfdc;
        border-radius: 3px;
        color: #514557;
        font-size: .85rem;
        cursor: pointer;
        transition: .18s ease;
    }
    .preview-checkboxes label:hover { border-color: #5B2B76; background: #faf7fb; }
    .preview-checkboxes input { position: absolute; opacity: 0; pointer-events: none; }
    .preview-checkbox-mark {
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid #bfb4c3;
        border-radius: 2px;
        color: transparent;
        font-size: .65rem;
    }
    .preview-checkboxes input:checked + .preview-checkbox-mark { border-color: #5B2B76; background: #5B2B76; color: #fff; }
    .preview-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 16px 30px;
        border-top: 1px solid #e5e0e7;
        background: #f7f5f8;
        color: #756a7a;
        font-size: .78rem;
    }
    .preview-progress { color: #756a7a; font-size: .78rem; font-weight: 800; }
    .preview-nav-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1px solid #d4cad8;
        border-radius: 3px;
        font-size: .8rem;
        font-weight: 800;
    }
    .preview-prev { background: #fff; color: #5f5464; }
    .preview-prev.is-hidden { visibility: hidden; }
    .preview-next { border-color: #5B2B76; background: #5B2B76; color: #fff; }
    .preview-next:hover { background: #3d174f; }
    .preview-empty { padding: 70px 20px; color: #8b818f; text-align: center; }
    .preview-empty i { margin-bottom: 12px; color: #117E8C; font-size: 1.8rem; }

    /* Estilo para el drag */
    .sortable-ghost {
        opacity: 0.4;
        background: #eef2ff;
    }
    
    .sortable-drag {
        transform: scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }

    @media (max-width: 640px) {
        .preview-modal { padding: 10px; }
        .preview-dialog { max-height: calc(100vh - 20px); }
        .preview-side-art { display: none !important; }
        .preview-cover { padding: 42px 18px; }
        .preview-header, .preview-body, .preview-footer { padding-left: 18px; padding-right: 18px; }
        .preview-question { grid-template-columns: 28px minmax(0,1fr); gap: 11px; }
        .preview-question-number { width: 28px; height: 28px; }
        .preview-footer { align-items: stretch; flex-direction: column; }
        .preview-checkboxes { grid-template-columns: 1fr; }
        .preview-nav-button { flex: 1; }
        #questionnaire-index > .w-full > :not(.rp-banner) {
            margin-left: 1rem;
            margin-right: 1rem;
        }
        .rp-banner .px-8 { 
            padding-left: 1.25rem; 
            padding-right: 1.25rem; 
        }
        #questionnaire-index .login-mosaic { display: none; }
        #questionnaire-index .back-button { width: auto; justify-content: flex-start; }
        #questionnaire-index .page-actions .new-topic-button { width: 100%; justify-content: center; }
    }
</style>
@endsection
