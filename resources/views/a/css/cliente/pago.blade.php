<style>
    :root {
        --prodovi-purple: #5b2b76;
        --prodovi-orange: #ef6c22;
        --prodovi-turquoise: #117e8c;
        --prodovi-green: #7da533;
        --prodovi-gold: #f5a900;
        --surface: #0d0d0f;
        --surface-soft: #151417;
        --border: rgba(255, 255, 255, .09);
        --text: #f7f5f8;
        --muted: #9b949e;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { color-scheme: dark; }

    body {
        min-height: 100vh;
        overflow-x: hidden;
        background: #000;
        color: var(--text);
        font-family: 'Varela Round', sans-serif;
        line-height: 1.6;
    }

    body::before,
    body::after {
        content: '';
        position: fixed;
        z-index: 0;
        width: 210px;
        height: 210px;
        border-radius: 28px;
        pointer-events: none;
        opacity: .2;
    }

    body::before { top: 17%; left: -145px; background: var(--prodovi-purple); transform: rotate(24deg); }
    body::after { right: -155px; bottom: 4%; background: var(--prodovi-turquoise); transform: rotate(-18deg); }

    .main-container {
        position: relative;
        z-index: 2;
        width: min(1180px, calc(100% - 40px));
        min-height: 100vh;
        margin-inline: auto;
        padding: 118px 0 72px;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin-bottom: 25px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--surface);
        color: #fff;
        text-decoration: none;
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }

    .back-button:hover { border-color: var(--prodovi-turquoise); background: #121719; transform: translateX(-3px); }

    .payment-header {
        position: relative;
        min-height: 205px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-bottom: 26px;
        padding: 35px 300px 35px 38px;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 22px;
        background: var(--surface);
    }

    .payment-kicker {
        position: relative;
        z-index: 2;
        width: max-content;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: var(--prodovi-turquoise);
        font-size: .67rem;
        font-weight: 800;
        letter-spacing: .15em;
        text-decoration: none;
        text-transform: uppercase;
        transition: color .2s ease, transform .2s ease;
    }

    .payment-kicker:hover { color: var(--prodovi-orange); transform: translateX(-3px); }

    .payment-title {
        position: relative;
        z-index: 2;
        margin: 0;
        color: #fff;
        font-family: 'Rowdies', sans-serif;
        font-size: clamp(2.25rem, 4vw, 2.5rem);
        font-weight: 600;
        letter-spacing: -.045em;
        line-height: 1;
    }

    .plan-name {
        position: relative;
        z-index: 2;
        margin-top: 14px;
        color: var(--prodovi-gold);
        font-size: clamp(1rem, 2vw, 1.35rem);
        font-weight: 700;
        text-transform: capitalize;
    }

    .payment-mosaic {
        position: absolute;
        right: 34px;
        bottom: 28px;
        width: 220px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 63px);
        gap: 7px;
        pointer-events: none;
    }

    .payment-mosaic span { display: block; border-radius: 7px; }
    .payment-mosaic span:nth-child(1) { background: var(--prodovi-orange); clip-path: polygon(0 0,100% 0,100% 68%,68% 68%,68% 100%,0 100%); }
    .payment-mosaic span:nth-child(2) { background: var(--prodovi-purple); clip-path: polygon(18% 0,100% 0,100% 82%,82% 100%,0 100%,0 18%); }
    .payment-mosaic span:nth-child(3) { background: var(--prodovi-turquoise); clip-path: polygon(0 0,74% 0,74% 24%,100% 24%,100% 100%,0 100%); }
    .payment-mosaic span:nth-child(4) { background: var(--prodovi-green); clip-path: polygon(0 0,100% 0,100% 78%,78% 100%,0 100%); }
    .payment-mosaic span:nth-child(5) { background: var(--prodovi-gold); clip-path: polygon(0 0,100% 0,100% 100%,24% 100%,24% 76%,0 76%); }
    .payment-mosaic span:nth-child(6) { background: var(--prodovi-orange); clip-path: polygon(0 0,82% 0,100% 18%,100% 100%,18% 100%,0 82%); }

    .payment-container {
        display: grid;
        grid-template-columns: minmax(300px, .82fr) minmax(0, 1.18fr);
        align-items: start;
        gap: 22px;
        margin-bottom: 22px;
    }

    .payment-summary-column, .payment-options-column { min-width: 0; }

    .payment-summary,
    .payment-option {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--surface);
        box-shadow: 0 20px 55px rgba(0, 0, 0, .24);
    }

    .payment-summary { position: sticky; top: 105px; padding: 28px; }

    .summary-title {
        margin-bottom: 22px;
        padding-bottom: 17px;
        border-bottom: 1px solid var(--border);
        color: #fff;
        font-family: 'Rowdies', sans-serif;
        font-size: 1.25rem;
        font-weight: 400;
    }

    .summary-title::before { content: none; }

    .summary-details {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 13px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .06);
    }

    .summary-label { color: var(--muted); font-size: .83rem; }
    .summary-value { color: #fff; font-size: .84rem; font-weight: 700; text-align: right; }

    .total-amount {
        margin-top: 22px;
        padding: 18px;
        border-radius: 12px;
        background: var(--prodovi-purple);
        color: #fff;
        font-size: 1.05rem;
        font-weight: 800;
        text-align: center;
    }

    .payment-options { display: flex; flex-direction: column; gap: 14px; }

    .payment-option {
        position: relative;
        overflow: hidden;
        padding: 23px;
        transition: transform .2s ease, border-color .2s ease;
    }

    .payment-option:hover { border-color: rgba(17, 126, 140, .65); transform: translateY(-2px); }
    .payment-option:first-child { border-left: 4px solid var(--prodovi-turquoise); }
    .payment-option:last-child { border-left: 4px solid var(--prodovi-orange); }

    .option-header { display: flex; align-items: center; gap: 14px; cursor: pointer; }

    .option-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
    }

    .option-title i {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: var(--prodovi-turquoise);
        color: #fff;
    }

    .payment-option:last-child .option-title i { background: var(--prodovi-orange); }

    input[type="checkbox"] {
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        appearance: none;
        display: grid;
        place-items: center;
        border: 2px solid #4f4a52;
        border-radius: 7px;
        background: #18171a;
        cursor: pointer;
        transition: border-color .2s ease, background .2s ease;
    }

    input[type="checkbox"]:checked { border-color: var(--prodovi-turquoise); background: var(--prodovi-turquoise); }
    input[type="checkbox"]:checked::after { content: '✓'; color: #fff; font-size: .78rem; font-weight: 900; }

    .option-content { display: none; margin-top: 20px; animation: revealPayment .25s ease both; }
    @keyframes revealPayment { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: none; } }

    .billing-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--surface-soft);
    }

    .field-wide { grid-column: 1 / -1; }
    .field-group { min-width: 0; text-align: left; }
    .field-group label {
        display: block;
        margin-bottom: 6px;
        color: #d8d3da;
        font-size: .78rem;
        font-weight: 700;
    }

    .field-group input,
    .field-group select {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #403c43;
        border-radius: 8px;
        outline: none;
        background: #0c0c0d;
        color: #fff;
        font: inherit;
        font-size: .84rem;
    }

    .field-group input:focus,
    .field-group select:focus { border-color: var(--prodovi-turquoise); }

    .qr-code,
    .physical-payment {
        padding: 22px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--surface-soft);
        color: var(--muted);
        text-align: center;
    }

    .qr-code img {
        width: min(230px, 100%);
        padding: 10px;
        border: 3px solid var(--prodovi-turquoise);
        border-radius: 14px;
        background: #fff;
    }

    .qr-code p, .physical-payment p { margin-top: 15px; color: var(--muted); font-size: .84rem; }
    .physical-payment p:first-child { margin-top: 0; }

    .payment-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 16px;
        color: #7fd1da;
        font-size: .83rem;
        font-weight: 700;
        text-decoration: none;
    }

    .payment-alert {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2147483647;
        width: min(400px, calc(100% - 40px));
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 18px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 10px;
        background: #117e8c;
        color: #fff;
        box-shadow: 0 12px 35px rgba(0,0,0,.4);
    }

    .payment-alert span { min-width: 0; flex: 1; }
    .payment-alert button {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        border: 0;
        background: transparent;
        color: #fff;
        cursor: pointer;
    }

    .payment-code {
        display: inline-block;
        margin: 18px 0 2px;
        padding: 12px 18px;
        border: 2px dashed var(--prodovi-orange);
        border-radius: 10px;
        background: #0c0c0d;
        color: #fff;
        font-family: 'Courier New', monospace;
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: .12em;
    }

    .done-btn,
    .modal-btn {
        min-height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 0;
        border-radius: 11px;
        background: var(--prodovi-orange);
        color: #fff;
        font-weight: 800;
        cursor: pointer;
        transition: transform .2s ease, filter .2s ease;
    }

    .done-btn { width: min(100%, 360px); margin: 28px auto 0; padding: 12px 24px; font-size: 1rem; }
    .done-btn:hover, .modal-btn:hover { filter: brightness(1.08); transform: translateY(-2px); }
    .done-btn:disabled { opacity: .6; cursor: wait; transform: none; }

    .modal {
        position: fixed;
        inset: 0;
        z-index: 2147483647;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(0, 0, 0, .82);
    }

    .modal-content {
        width: min(440px, 100%);
        padding: 36px;
        border: 1px solid var(--border);
        border-radius: 20px;
        background: var(--surface);
        box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
        text-align: center;
        animation: modalIn .25s ease both;
    }

    @keyframes modalIn { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }

    .modal-title { margin-bottom: 13px; color: var(--prodovi-turquoise); font-family: 'Rowdies', sans-serif; font-size: 1.7rem; }
    .modal-content p { margin-bottom: 24px; color: var(--muted); }
    .modal-btn { width: 100%; padding: 12px 20px; background: var(--prodovi-turquoise); }

    .physical-modal-content { width: min(500px, 100%); }
    .physical-modal-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: rgba(125, 165, 51, .18);
        color: #a9d457;
        font-size: 1.55rem;
    }

    .physical-modal-content .payment-code { display: block; margin: 0 0 20px; }
    .physical-modal-content > p { margin-bottom: 12px; }

    .office-links {
        display: grid;
        gap: 9px;
        margin-bottom: 20px;
        text-align: left;
    }

    .office-links a {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface-soft);
        color: #e7e2e9;
        font-size: .8rem;
        line-height: 1.4;
        text-decoration: none;
    }

    .office-links a i { width: 20px; flex: 0 0 20px; color: #7fd1da; text-align: center; }
    .office-links a:last-child i { color: #5fd276; }
    .download-code-btn { margin-bottom: 10px; background: var(--prodovi-orange); }
    .close-physical-btn { background: var(--prodovi-turquoise); }
    .close-physical-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
        filter: none;
        transform: none;
    }

    @media (max-width: 900px) {
        .main-container { width: min(100% - 32px, 1180px); padding-top: 105px; }
        .payment-header { padding-right: 245px; }
        .payment-mosaic { width: 180px; grid-template-rows: repeat(2, 52px); }
        .payment-container { grid-template-columns: 1fr; }
        .payment-summary { position: static; }
    }

    @media (max-width: 620px) {
        .main-container { width: min(100% - 24px, 1180px); padding: 90px 0 48px; }
        .back-button { width: 40px; height: 40px; margin-bottom: 16px; }
        .payment-header { min-height: 225px; padding: 27px 112px 27px 20px; border-radius: 17px; }
        .payment-title { font-size: clamp(1.2rem, 10vw, 1.9rem); }
        .plan-name { font-size: .92rem; }
        .payment-mosaic { right: 10px; bottom: 15px; width: 94px; grid-template-rows: repeat(2, 29px); gap: 3px; }
        .payment-mosaic span { border-radius: 4px; }
        .payment-container { gap: 12px; }
        .payment-summary, .payment-option { padding: 20px; border-radius: 15px; }
        .summary-details { align-items: flex-start; flex-direction: column; gap: 3px; }
        .summary-value { text-align: left; }
        .option-title { font-size: .9rem; }
        .option-title i { width: 32px; height: 32px; }
        .qr-code, .physical-payment { padding: 16px; }
        .billing-form { grid-template-columns: 1fr; padding: 16px; }
        .field-wide { grid-column: auto; }
        .modal-content { padding: 28px 21px; border-radius: 17px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation: none !important; scroll-behavior: auto !important; }
    }
</style>
