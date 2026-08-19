@if($empresaCuestionario && $temasCuestionario->isNotEmpty())
@php
    $mqQuestions = $temasCuestionario->flatMap->preguntas->values();
    $mqStep = 0;
    $mqQuestionNumber = 0;
@endphp

<div id="mandatory-questionnaire" class="mq-modal" role="dialog" aria-modal="true" aria-labelledby="mq-title">
    <div class="mq-backdrop"></div>
    <div class="mq-dialog">
        <header class="mq-header">
            <span class="mq-kicker">Cuéntanos sobre tu marca</span>
            <h2 id="mq-title">Brief inicial de {{ $empresaCuestionario->nombre_empresa }}</h2>
            <div class="mq-timeline" aria-label="Progreso del cuestionario">
                @foreach($mqQuestions as $questionIndex => $question)
                    <span data-mq-timeline="{{ $questionIndex }}">{{ $questionIndex + 1 }}</span>
                @endforeach
            </div>
        </header>

        <form id="mandatory-questionnaire-form" action="{{ route('empresas.cuestionario.store', $empresaCuestionario->id) }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="from_dashboard" value="1">

            <div class="mq-slides">
                @foreach($temasCuestionario as $tema)
                    <section class="mq-slide {{ $mqStep === 0 ? 'is-active' : '' }} mq-cover" data-mq-step="{{ $mqStep }}" data-mq-cover>
                        <div class="mq-side-art mq-side-left" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                        <div class="mq-side-art mq-side-right" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                        <div class="mq-cover-content">
                            <span>Sección del cuestionario</span>
                            <h3>{{ $tema->nombre_tema }}</h3>
                            <p>{{ $tema->descripcion_tema ?: 'Responde estas preguntas para ayudarnos a conocer mejor tu marca.' }}</p>
                            <small><i class="fas fa-list-check"></i> {{ $tema->preguntas->count() }} pregunta{{ $tema->preguntas->count() === 1 ? '' : 's' }}</small>
                        </div>
                    </section>
                    @php
                        $mqStep++;
                    @endphp

                    @foreach($tema->preguntas as $pregunta)
                        @php
                            $mqQuestionNumber++;
                            $fieldName = 'respuesta_'.$pregunta->id;
                            $storedAnswer = old($fieldName, $respuestasCuestionario[$pregunta->id] ?? '');
                            $markedAnswers = is_array($storedAnswer)
                                ? $storedAnswer
                                : array_values(array_filter(array_map('trim', explode(' | ', (string) $storedAnswer))));
                            $otherAnswer = collect($markedAnswers)->first(fn ($value) => str_starts_with($value, 'Otro: '));
                            $selectedAnswer = $otherAnswer ? 'Otro' : (is_array($storedAnswer) ? '' : (string) $storedAnswer);
                        @endphp
                        <section class="mq-slide mq-question" data-mq-step="{{ $mqStep }}" data-mq-question="{{ $mqQuestionNumber - 1 }}">
                            <div class="mq-question-number">{{ $mqQuestionNumber }}</div>
                            <div class="mq-question-content">
                                <label for="mq-field-{{ $pregunta->id }}">
                                    {{ $pregunta->pregunta }}
                                    @if($pregunta->requerido)<strong>*</strong>@else<span class="mq-optional">(Opcional)</span>@endif
                                </label>
                                @if($pregunta->ayuda)<p>{{ $pregunta->ayuda }}</p>@endif

                                @if($pregunta->tipo_respuesta === 'opcion_multiple')
                                    <div class="mq-custom-select" data-mq-select>
                                        <input id="mq-field-{{ $pregunta->id }}" type="hidden" name="{{ $fieldName }}" value="{{ $selectedAnswer }}" data-mq-required="{{ $pregunta->requerido ? '1' : '0' }}">
                                        <button type="button" class="mq-select-trigger" aria-expanded="false">
                                            <span>{{ $selectedAnswer ?: 'Selecciona una opción' }}</span><i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="mq-select-menu">
                                            @foreach($pregunta->opciones ?? [] as $opcion)
                                                <button type="button" class="mq-select-option {{ $selectedAnswer === $opcion ? 'is-selected' : '' }}" data-value="{{ $opcion }}">
                                                    <span>{{ $opcion }}</span><i class="fas fa-check"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if(in_array('Otro', $pregunta->opciones ?? [], true))
                                        <textarea name="{{ $fieldName }}_otro" rows="1" class="mq-auto-grow mq-other {{ $selectedAnswer === 'Otro' ? '' : 'hidden' }}" data-mq-other-for="{{ $fieldName }}" placeholder="Especifica otra opción">{{ old($fieldName.'_otro', $otherAnswer ? str_replace('Otro: ', '', $otherAnswer) : '') }}</textarea>
                                    @endif
                                @elseif($pregunta->tipo_respuesta === 'checkbox')
                                    <div class="mq-checkboxes" data-mq-checkbox-group data-mq-required="{{ $pregunta->requerido ? '1' : '0' }}">
                                        @foreach($pregunta->opciones ?? [] as $opcion)
                                            @php
                                                $isMarked = in_array($opcion, $markedAnswers, true) || ($opcion === 'Otro' && $otherAnswer);
                                            @endphp
                                            <label>
                                                <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $opcion }}" {{ $isMarked ? 'checked' : '' }} data-mq-other-checkbox="{{ $opcion === 'Otro' ? $fieldName : '' }}">
                                                <span class="mq-check"><i class="fas fa-check"></i></span><span>{{ $opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @if(in_array('Otro', $pregunta->opciones ?? [], true))
                                        <textarea name="{{ $fieldName }}_otro" rows="1" class="mq-auto-grow mq-other {{ $otherAnswer ? '' : 'hidden' }}" data-mq-other-for="{{ $fieldName }}" placeholder="Cuéntanos cuál">{{ old($fieldName.'_otro', $otherAnswer ? str_replace('Otro: ', '', $otherAnswer) : '') }}</textarea>
                                    @endif
                                @elseif($pregunta->tipo_respuesta === 'texto_largo')
                                    <textarea id="mq-field-{{ $pregunta->id }}" name="{{ $fieldName }}" rows="4" class="mq-auto-grow" placeholder="Escribe tu respuesta..." {{ $pregunta->requerido ? 'required' : '' }}>{{ is_array($storedAnswer) ? '' : $storedAnswer }}</textarea>
                                @else
                                    <textarea id="mq-field-{{ $pregunta->id }}" name="{{ $fieldName }}" rows="1" class="mq-auto-grow mq-short" placeholder="Escribe tu respuesta..." {{ $pregunta->requerido ? 'required' : '' }}>{{ is_array($storedAnswer) ? '' : $storedAnswer }}</textarea>
                                @endif
                                <span class="mq-field-error">Completa esta respuesta para continuar.</span>
                            </div>
                        </section>
                        @php
                            $mqStep++;
                        @endphp
                    @endforeach
                @endforeach
            </div>

            <footer class="mq-footer">
                <button type="button" id="mq-prev" class="mq-button mq-prev" disabled><i class="fas fa-arrow-left"></i> Anterior</button>
                <span id="mq-progress"></span>
                <button type="button" id="mq-next" class="mq-button mq-next">Comenzar <i class="fas fa-arrow-right"></i></button>
            </footer>
        </form>
    </div>
</div>

<style>
    .mq-optional { margin-left:7px; color:#938799; font-size:.76rem; font-weight:600; }
    .mq-modal{position:fixed;inset:0;z-index:2147483000;display:flex;align-items:center;justify-content:center;padding:22px;font-family:Inter,system-ui,sans-serif}.mq-backdrop{position:absolute;inset:0;background:rgba(16,12,18,.78);backdrop-filter:blur(6px)}.mq-dialog{position:relative;width:min(900px,100%);max-height:calc(100vh - 44px);display:flex;flex-direction:column;overflow:hidden;border:1px solid rgba(255,255,255,.16);border-radius:3px;background:#fff;box-shadow:0 30px 90px rgba(0,0,0,.38)}.mq-header{padding:23px 30px;border-bottom:5px solid #EF6C22;background:#242426;color:#fff}.mq-kicker{display:block;color:#EF6C22;font-size:.68rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.mq-header h2{margin:7px 0 0;font-size:clamp(1.2rem,3vw,1.65rem);font-weight:800}.mq-timeline{display:flex;align-items:center;margin-top:17px;padding:4px 2px;overflow-x:auto}.mq-timeline span{position:relative;width:28px;height:28px;display:grid;place-items:center;flex:0 0 28px;margin-right:30px;border:1px solid #666269;border-radius:50%;background:#343436;color:#aaa5ad;font-size:.68rem;font-weight:900;transition:.25s}.mq-timeline span:last-child{margin-right:0}.mq-timeline span:not(:last-child)::after{content:'';position:absolute;left:28px;width:30px;height:2px;background:#555158}.mq-timeline span.is-active{border-color:#EF6C22;background:#EF6C22;color:#fff;box-shadow:0 0 0 4px rgba(239,108,34,.16)}.mq-timeline span.is-complete{border-color:#117E8C;background:#117E8C;color:#fff}.mq-timeline span.is-complete::after{background:#117E8C}.mq-slides{min-height:350px;overflow-y:auto;background:#fff}.mq-slide{display:none;min-height:350px;padding:34px 42px}.mq-slide.is-active{display:flex}.mq-cover{position:relative;align-items:center;justify-content:center;text-align:center;overflow:hidden}.mq-cover-content{position:relative;z-index:2;max-width:650px;padding:30px 72px}.mq-cover-content>span{color:#EF6C22;font-size:.68rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.mq-cover h3{margin:14px 0 0;color:#29222c;font-size:clamp(1.7rem,4vw,2.6rem);font-weight:900;line-height:1.08;letter-spacing:-.035em}.mq-cover p{margin:16px 0 0;color:#756b7a;font-size:.95rem;line-height:1.65}.mq-cover small{display:block;margin-top:23px;padding-top:15px;border-top:1px solid #e5e0e7;color:#5B2B76;font-weight:800}.mq-cover small i{margin-right:7px;color:#117E8C}.mq-side-art{position:absolute;top:50%;display:grid;gap:7px;transform:translateY(-50%);pointer-events:none}.mq-side-left{left:18px}.mq-side-right{right:18px}.mq-side-art i{width:52px;height:43px;display:block;border-radius:7px}.mq-side-art i:nth-child(1){background:#EF6C22;clip-path:polygon(0 0,100% 0,100% 68%,68% 68%,68% 100%,0 100%)}.mq-side-art i:nth-child(2){background:#F5A900;clip-path:polygon(0 0,100% 0,100% 100%,24% 100%,24% 76%,0 76%)}.mq-side-art i:nth-child(3){background:#5B2B76;clip-path:polygon(18% 0,100% 0,100% 82%,82% 100%,0 100%,0 18%)}.mq-side-art i:nth-child(4){background:#117E8C;clip-path:polygon(0 0,74% 0,74% 24%,100% 24%,100% 100%,0 100%)}.mq-side-art i:nth-child(5){background:#7DA533;clip-path:polygon(0 0,100% 0,100% 100%,34% 100%,34% 66%,0 66%)}.mq-side-right i{transform:scaleX(-1)}.mq-question{align-items:flex-start;gap:17px;padding-top:55px}.mq-question-number{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:2px;background:#5B2B76;color:#fff;font-size:.8rem;font-weight:900}.mq-question-content{width:100%;min-width:0}.mq-question-content>label{display:block;margin:0 0 8px;color:#28222b;font-size:1rem;font-weight:800}.mq-question-content>label strong{margin-left:4px;color:#d84c3f}.mq-question-content>p{margin:0 0 12px;color:#817786;font-size:.8rem}.mq-question textarea,.mq-select-trigger{width:100%;min-height:48px;padding:12px 14px;border:1px solid #d8cfdc;border-radius:3px;background:#fff;color:#342c38;font:inherit;font-size:.88rem;outline:0}.mq-question textarea:focus,.mq-select-trigger:focus{border-color:#5B2B76;box-shadow:0 0 0 3px rgba(91,43,118,.1)}.mq-auto-grow{overflow-y:hidden;resize:none}.mq-short{min-height:48px}.mq-custom-select{position:relative}.mq-select-trigger{display:flex;align-items:center;justify-content:space-between;text-align:left;cursor:pointer}.mq-select-trigger i{color:#5B2B76;transition:.2s}.mq-custom-select.is-open .mq-select-trigger i{transform:rotate(180deg)}.mq-select-menu{position:absolute;z-index:20;top:calc(100% + 5px);right:0;left:0;display:none;padding:7px;border:1px solid #d8cfdc;border-radius:3px;background:#fff;box-shadow:0 16px 35px rgba(44,30,50,.18)}.mq-custom-select.is-open .mq-select-menu{display:grid;gap:3px}.mq-select-option{display:flex;align-items:center;justify-content:space-between;padding:10px;border:0;border-radius:2px;background:#fff;color:#514557;text-align:left;cursor:pointer}.mq-select-option:hover,.mq-select-option.is-selected{background:#f5eff7;color:#5B2B76}.mq-select-option i{opacity:0}.mq-select-option.is-selected i{opacity:1}.mq-checkboxes{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.mq-checkboxes label{display:flex;align-items:center;gap:10px;min-height:47px;margin:0;padding:10px 12px;border:1px solid #d8cfdc;border-radius:3px;color:#514557;font-size:.85rem;cursor:pointer}.mq-checkboxes label:hover{border-color:#5B2B76;background:#faf7fb}.mq-checkboxes input{position:absolute;opacity:0;pointer-events:none}.mq-check{width:20px;height:20px;display:grid;place-items:center;flex:0 0 auto;border:1px solid #bfb4c3;border-radius:2px;color:transparent;font-size:.65rem}.mq-checkboxes input:checked+.mq-check{border-color:#5B2B76;background:#5B2B76;color:#fff}.mq-other{margin-top:10px}.mq-field-error{display:none;margin-top:7px;color:#c83d35;font-size:.75rem;font-weight:700}.mq-question-content.has-error .mq-field-error{display:block}.mq-question-content.has-error textarea,.mq-question-content.has-error .mq-select-trigger,.mq-question-content.has-error .mq-checkboxes{border-color:#c83d35}.mq-footer{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:15px 30px;border-top:1px solid #e5e0e7;background:#f7f5f8}.mq-footer>span{color:#756a7a;font-size:.78rem;font-weight:800}.mq-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border:1px solid #d4cad8;border-radius:3px;font-size:.8rem;font-weight:800;cursor:pointer}.mq-prev{background:#fff;color:#5f5464}.mq-prev:disabled{visibility:hidden}.mq-next{border-color:#5B2B76;background:#5B2B76;color:#fff}.mq-next:hover{background:#3d174f}.mq-hidden,.mq-other.hidden{display:none!important}
    @media(max-width:640px){.mq-modal{padding:8px}.mq-dialog{max-height:calc(100vh - 16px)}.mq-header,.mq-slide,.mq-footer{padding-left:18px;padding-right:18px}.mq-slides,.mq-slide{min-height:390px}.mq-cover-content{padding:28px 6px}.mq-side-art{display:none}.mq-question{padding-top:35px}.mq-checkboxes{grid-template-columns:1fr}.mq-footer{gap:8px}.mq-button{flex:1;padding-inline:10px}.mq-footer>span{font-size:.7rem;text-align:center}}
</style>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const modal=document.getElementById('mandatory-questionnaire');
    const form=document.getElementById('mandatory-questionnaire-form');
    if(!modal||!form)return;
    document.body.appendChild(modal);
    document.body.style.overflow='hidden';
    const slides=Array.from(modal.querySelectorAll('[data-mq-step]'));
    const timeline=Array.from(modal.querySelectorAll('[data-mq-timeline]'));
    const prev=document.getElementById('mq-prev');
    const next=document.getElementById('mq-next');
    const progress=document.getElementById('mq-progress');
    let step=0;

    const autosize=field=>{field.style.height='auto';field.style.height=`${field.scrollHeight}px`;};
    modal.querySelectorAll('.mq-auto-grow').forEach(field=>{field.addEventListener('input',()=>autosize(field));autosize(field);});

    modal.querySelectorAll('[data-mq-select]').forEach(select=>{
        const input=select.querySelector('input[type="hidden"]');
        const trigger=select.querySelector('.mq-select-trigger');
        const label=trigger.querySelector('span');
        const close=()=>{select.classList.remove('is-open');trigger.setAttribute('aria-expanded','false');};
        trigger.addEventListener('click',()=>{const opening=!select.classList.contains('is-open');modal.querySelectorAll('.mq-custom-select.is-open').forEach(item=>item.classList.remove('is-open'));if(opening){select.classList.add('is-open');trigger.setAttribute('aria-expanded','true');}});
        select.querySelectorAll('.mq-select-option').forEach(option=>option.addEventListener('click',()=>{
            input.value=option.dataset.value;label.textContent=option.dataset.value;
            select.querySelectorAll('.mq-select-option').forEach(item=>item.classList.toggle('is-selected',item===option));
            const other=form.querySelector(`[data-mq-other-for="${input.name}"]`);
            other?.classList.toggle('hidden',option.dataset.value!=='Otro');
            input.closest('.mq-question-content').classList.remove('has-error');close();
        }));
    });

    modal.querySelectorAll('[data-mq-other-checkbox]').forEach(checkbox=>checkbox.addEventListener('change',()=>{
        if(!checkbox.dataset.mqOtherCheckbox)return;
        form.querySelector(`[data-mq-other-for="${checkbox.dataset.mqOtherCheckbox}"]`)?.classList.toggle('hidden',!checkbox.checked);
    }));
    document.addEventListener('click',event=>{if(!event.target.closest('.mq-custom-select'))modal.querySelectorAll('.mq-custom-select.is-open').forEach(item=>item.classList.remove('is-open'));});

    function validateCurrent(){
        const current=slides[step];
        if(!current?.dataset.mqQuestion)return true;
        const content=current.querySelector('.mq-question-content');
        let valid=true;
        const requiredText=current.querySelector('textarea[required]');
        const requiredSelect=current.querySelector('input[type="hidden"][data-mq-required="1"]');
        const requiredChecks=current.querySelector('[data-mq-checkbox-group][data-mq-required="1"]');
        if(requiredText&&!requiredText.value.trim())valid=false;
        if(requiredSelect&&!requiredSelect.value)valid=false;
        if(requiredChecks&&!requiredChecks.querySelector('input:checked'))valid=false;
        content.classList.toggle('has-error',!valid);
        if(!valid)(requiredText||current.querySelector('.mq-select-trigger')||requiredChecks)?.focus?.();
        return valid;
    }

    function render(){
        slides.forEach((slide,index)=>slide.classList.toggle('is-active',index===step));
        const current=slides[step];
        const questionIndex=current?.dataset.mqQuestion==null?-1:Number(current.dataset.mqQuestion);
        timeline.forEach((item,index)=>{item.classList.toggle('is-active',index===questionIndex);item.classList.toggle('is-complete',questionIndex>index||step>Number(slides.find(slide=>slide.dataset.mqQuestion===String(index))?.dataset.mqStep));});
        prev.disabled=step===0;
        progress.textContent=questionIndex<0?'':`Pregunta ${questionIndex+1} de ${timeline.length}`;
        next.innerHTML=current?.hasAttribute('data-mq-cover')?'Comenzar <i class="fas fa-arrow-right"></i>':step===slides.length-1?'Enviar respuestas <i class="fas fa-check"></i>':'Siguiente <i class="fas fa-arrow-right"></i>';
        modal.querySelector('.mq-slides').scrollTop=0;
    }
    prev.addEventListener('click',()=>{if(step>0){step--;render();}});
    next.addEventListener('click',()=>{
        if(!validateCurrent())return;
        if(step<slides.length-1){step++;render();return;}
        next.disabled=true;next.innerHTML='<i class="fas fa-spinner fa-spin"></i> Guardando...';form.submit();
    });
    form.addEventListener('keydown',event=>{if(event.key==='Enter'&&event.target.tagName!=='TEXTAREA'){event.preventDefault();next.click();}});
    render();
});
</script>
@endif
