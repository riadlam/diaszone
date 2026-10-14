@extends('layouts.app')

@section('title', __('giveaway.page_title'))

@push('styles')
<style>
.dz-gw {
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --accent: #6d28d9;
    --soft: #f5f3ff;
}
.dz-gw-shell {
    position: relative;
    overflow-x: hidden;
    overflow-y: visible;
    background:
        radial-gradient(900px 400px at 50% -10%, rgba(109, 40, 217, 0.16), transparent 55%),
        radial-gradient(700px 320px at 50% 100%, rgba(37, 99, 235, 0.08), transparent 50%),
        #f8fafc;
    min-height: calc(100vh - 4rem);
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
}
.dz-gw-wrap {
    width: 100%;
    max-width: 680px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}
.dz-gw-floaters {
    pointer-events: none;
    position: absolute;
    inset: 0;
    overflow: hidden;
    z-index: 0;
}
.dz-gw-floater {
    position: absolute;
    border-radius: 9999px;
    background: rgba(124, 58, 237, 0.28);
    box-shadow: 0 0 24px rgba(124, 58, 237, 0.35);
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    will-change: transform, opacity;
    -webkit-animation: dz-gw-float 7s ease-in-out infinite;
    animation: dz-gw-float 7s ease-in-out infinite;
}
.dz-gw-floater:nth-child(1) { width: 22px; height: 22px; left: 6%; top: 16%; -webkit-animation-duration: 6.5s; animation-duration: 6.5s; }
.dz-gw-floater:nth-child(2) { width: 28px; height: 28px; right: 8%; top: 24%; background: rgba(37,99,235,.28); box-shadow: 0 0 28px rgba(37,99,235,.35); -webkit-animation-duration: 8s; animation-duration: 8s; -webkit-animation-delay: -1.5s; animation-delay: -1.5s; }
.dz-gw-floater:nth-child(3) { width: 16px; height: 16px; left: 16%; bottom: 18%; -webkit-animation-duration: 7.2s; animation-duration: 7.2s; -webkit-animation-delay: -3s; animation-delay: -3s; }
.dz-gw-floater:nth-child(4) { width: 24px; height: 24px; right: 16%; bottom: 26%; background: rgba(251,191,36,.42); box-shadow: 0 0 26px rgba(251,191,36,.4); -webkit-animation-duration: 9s; animation-duration: 9s; -webkit-animation-delay: -.8s; animation-delay: -.8s; }
.dz-gw-floater:nth-child(5) { width: 14px; height: 14px; left: 46%; top: 10%; -webkit-animation-duration: 5.8s; animation-duration: 5.8s; -webkit-animation-delay: -2.2s; animation-delay: -2.2s; }
.dz-gw-floater:nth-child(6) { width: 18px; height: 18px; left: 72%; top: 55%; background: rgba(236,72,153,.28); box-shadow: 0 0 22px rgba(236,72,153,.35); -webkit-animation-duration: 7.8s; animation-duration: 7.8s; -webkit-animation-delay: -4s; animation-delay: -4s; }
@-webkit-keyframes dz-gw-float {
    0%, 100% { -webkit-transform: translate3d(0, 0, 0) scale(1); opacity: .45; }
    50% { -webkit-transform: translate3d(12px, -28px, 0) scale(1.15); opacity: 1; }
}
@keyframes dz-gw-float {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); opacity: .45; }
    50% { transform: translate3d(12px, -28px, 0) scale(1.15); opacity: 1; }
}
.dz-gw-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.35rem;
    min-height: 200px;
    color: #fff;
    text-align: center;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    -webkit-animation: dz-gw-rise .55s ease-out both;
    animation: dz-gw-rise .55s ease-out both;
}
@media (min-width: 640px) {
    .dz-gw-hero { min-height: 230px; }
}
.dz-gw-hero__bg {
    position: absolute;
    inset: -8%;
    width: 116%;
    height: 116%;
    object-fit: cover;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    will-change: transform;
    -webkit-animation: dz-gw-ken 12s ease-in-out infinite alternate;
    animation: dz-gw-ken 12s ease-in-out infinite alternate;
}
@-webkit-keyframes dz-gw-ken {
    from { -webkit-transform: translate3d(0, 0, 0) scale(1); }
    to { -webkit-transform: translate3d(-3%, -2%, 0) scale(1.12); }
}
@keyframes dz-gw-ken {
    from { transform: translate3d(0, 0, 0) scale(1); }
    to { transform: translate3d(-3%, -2%, 0) scale(1.12); }
}
.dz-gw-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(165deg, rgba(15, 23, 42, 0.84) 0%, rgba(76, 29, 149, 0.72) 55%, rgba(15, 23, 42, 0.8) 100%);
}
.dz-gw-hero__shine {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,.14) 48%, transparent 62%);
    -webkit-transform: translate3d(-120%, 0, 0);
    transform: translate3d(-120%, 0, 0);
    -webkit-animation: dz-gw-shine 3.8s ease-in-out infinite;
    animation: dz-gw-shine 3.8s ease-in-out infinite;
}
@-webkit-keyframes dz-gw-shine {
    0%, 35% { -webkit-transform: translate3d(-120%, 0, 0); }
    65%, 100% { -webkit-transform: translate3d(120%, 0, 0); }
}
@keyframes dz-gw-shine {
    0%, 35% { transform: translate3d(-120%, 0, 0); }
    65%, 100% { transform: translate3d(120%, 0, 0); }
}
.dz-gw-hero__content {
    position: relative;
    z-index: 2;
    padding: 1.35rem 1.1rem 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}
@media (min-width: 640px) {
    .dz-gw-hero__content { padding: 1.75rem 1.75rem 1.5rem; }
}
.dz-gw-count {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.4rem;
    width: 100%;
    max-width: 20rem;
    margin: 0 auto;
}
.dz-gw-count__cell {
    position: relative;
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.22);
    border-radius: 0.8rem;
    padding: 0.55rem 0.3rem;
    text-align: center;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    will-change: transform;
    -webkit-animation: dz-gw-pulse 1.8s ease-in-out infinite;
    animation: dz-gw-pulse 1.8s ease-in-out infinite;
}
.dz-gw-count__cell:nth-child(2) { -webkit-animation-delay: .12s; animation-delay: .12s; }
.dz-gw-count__cell:nth-child(3) { -webkit-animation-delay: .24s; animation-delay: .24s; }
.dz-gw-count__cell:nth-child(4) { -webkit-animation-delay: .36s; animation-delay: .36s; }
@-webkit-keyframes dz-gw-pulse {
    0%, 100% { -webkit-transform: translate3d(0, 0, 0) scale(1); box-shadow: 0 0 0 0 rgba(167,139,250,0); }
    50% { -webkit-transform: translate3d(0, -5px, 0) scale(1.04); box-shadow: 0 10px 22px rgba(167,139,250,.35); }
}
@keyframes dz-gw-pulse {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); box-shadow: 0 0 0 0 rgba(167,139,250,0); }
    50% { transform: translate3d(0, -5px, 0) scale(1.04); box-shadow: 0 10px 22px rgba(167,139,250,.35); }
}
.dz-gw-count__num {
    display: block;
    font-size: 1.25rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
}
.dz-gw-count__lbl {
    display: block;
    font-size: 0.58rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    opacity: 0.78;
    margin-top: 0.15rem;
}
.dz-gw-panel {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.2rem;
    padding: 1rem 1rem;
    text-align: center;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    -webkit-animation: dz-gw-rise .55s ease-out both;
    animation: dz-gw-rise .55s ease-out both;
}
.dz-gw-panel:nth-of-type(2) { -webkit-animation-delay: .05s; animation-delay: .05s; }
.dz-gw-panel:nth-of-type(3) { -webkit-animation-delay: .1s; animation-delay: .1s; }
.dz-gw-panel:nth-of-type(4) { -webkit-animation-delay: .15s; animation-delay: .15s; }
@-webkit-keyframes dz-gw-rise {
    from { opacity: 0; -webkit-transform: translate3d(0, 14px, 0); }
    to { opacity: 1; -webkit-transform: translate3d(0, 0, 0); }
}
@keyframes dz-gw-rise {
    from { opacity: 0; transform: translate3d(0, 14px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
@media (min-width: 640px) {
    .dz-gw-panel { padding: 1.25rem 1.35rem; }
}
.dz-gw-steps {
    display: grid;
    gap: 0.55rem;
    text-align: left;
}
.dz-gw-step-row {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.7rem;
    align-items: center;
    padding: 0.7rem 0.75rem;
    border-radius: 0.95rem;
    border: 1px solid var(--line);
    background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
    -webkit-animation: dz-gw-steprow 3.6s ease-in-out infinite;
    animation: dz-gw-steprow 3.6s ease-in-out infinite;
}
.dz-gw-step-row:nth-child(2) { -webkit-animation-delay: .4s; animation-delay: .4s; }
.dz-gw-step-row:nth-child(3) { -webkit-animation-delay: .8s; animation-delay: .8s; }
@-webkit-keyframes dz-gw-steprow {
    0%, 100% { border-color: #e2e8f0; box-shadow: 0 0 0 rgba(109,40,217,0); }
    50% { border-color: #c4b5fd; box-shadow: 0 6px 18px rgba(109,40,217,.08); }
}
@keyframes dz-gw-steprow {
    0%, 100% { border-color: #e2e8f0; box-shadow: 0 0 0 rgba(109,40,217,0); }
    50% { border-color: #c4b5fd; box-shadow: 0 6px 18px rgba(109,40,217,.08); }
}
.dz-gw-step-row__num {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 9999px;
    background: #ede9fe;
    color: #6d28d9;
    font-size: 0.8rem;
    font-weight: 900;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    will-change: transform;
    -webkit-animation: dz-gw-softbeat 1.6s ease-in-out infinite;
    animation: dz-gw-softbeat 1.6s ease-in-out infinite;
}
.dz-gw-step-row:nth-child(2) .dz-gw-step-row__num { -webkit-animation-delay: .25s; animation-delay: .25s; }
.dz-gw-step-row:nth-child(3) .dz-gw-step-row__num { -webkit-animation-delay: .5s; animation-delay: .5s; }
@-webkit-keyframes dz-gw-softbeat {
    0%, 100% { -webkit-transform: scale3d(1, 1, 1); background: #ede9fe; }
    50% { -webkit-transform: scale3d(1.18, 1.18, 1); background: #ddd6fe; }
}
@keyframes dz-gw-softbeat {
    0%, 100% { transform: scale3d(1, 1, 1); background: #ede9fe; }
    50% { transform: scale3d(1.18, 1.18, 1); background: #ddd6fe; }
}
.dz-gw-step-row__icon {
    width: 2rem;
    height: 2rem;
    object-fit: contain;
    flex-shrink: 0;
    -webkit-animation: dz-gw-wiggle 2.8s ease-in-out infinite;
    animation: dz-gw-wiggle 2.8s ease-in-out infinite;
}
.dz-gw-step-row:nth-child(2) .dz-gw-step-row__icon { -webkit-animation-delay: .35s; animation-delay: .35s; }
.dz-gw-step-row:nth-child(3) .dz-gw-step-row__icon { -webkit-animation-delay: .7s; animation-delay: .7s; }
@-webkit-keyframes dz-gw-wiggle {
    0%, 100% { -webkit-transform: rotate(0deg) translate3d(0,0,0); }
    25% { -webkit-transform: rotate(-6deg) translate3d(0,-2px,0); }
    75% { -webkit-transform: rotate(6deg) translate3d(0,2px,0); }
}
@keyframes dz-gw-wiggle {
    0%, 100% { transform: rotate(0deg) translate3d(0,0,0); }
    25% { transform: rotate(-6deg) translate3d(0,-2px,0); }
    75% { transform: rotate(6deg) translate3d(0,2px,0); }
}
.dz-gw-step-row h3 {
    font-size: 0.9rem;
    font-weight: 900;
    color: var(--ink);
    line-height: 1.2;
}
.dz-gw-step-row p {
    margin-top: 0.15rem;
    font-size: 0.75rem;
    color: var(--muted);
    line-height: 1.35;
}
.dz-gw-step-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.45rem;
}
.dz-gw-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
}
/* Enrolled — soft breathe grid (extra bottom room so pills aren’t cropped) */
.dz-gw-enrolled {
    overflow: visible;
    padding: 0.95rem 0.65rem 1.35rem;
}
.dz-gw-enrolled__grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-content: flex-start;
    gap: 0.75rem 0.7rem;
    padding: 1rem 0.35rem 1.5rem;
    min-height: 4rem;
    overflow: visible;
}
.dz-gw-name {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #f5f3ff;
    color: #5b21b6;
    border: 1px solid #ddd6fe;
    border-radius: 9999px;
    padding: 0.5rem 0.95rem;
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
    will-change: transform, box-shadow;
    -webkit-animation: dz-gw-breathe 3.4s ease-in-out infinite;
    animation: dz-gw-breathe 3.4s ease-in-out infinite;
}
.dz-gw-name.is-empty {
    color: #7c3aed;
    border-style: dashed;
    background: #faf5ff;
}
.dz-gw-name::before {
    content: "";
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 9999px;
    background: #22c55e;
    flex-shrink: 0;
}
.dz-gw-name.is-empty::before { background: #a78bfa; }
@-webkit-keyframes dz-gw-breathe {
    0%, 100% { -webkit-transform: translate3d(0, 0, 0) scale(1); box-shadow: 0 2px 0 rgba(109,40,217,0); }
    50% { -webkit-transform: translate3d(0, -2px, 0) scale(1.02); box-shadow: 0 8px 16px rgba(109,40,217,.1); }
}
@keyframes dz-gw-breathe {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); box-shadow: 0 2px 0 rgba(109,40,217,0); }
    50% { transform: translate3d(0, -2px, 0) scale(1.02); box-shadow: 0 8px 16px rgba(109,40,217,.1); }
}
.dz-gw-name:nth-child(3n) { -webkit-animation-delay: .35s; animation-delay: .35s; }
.dz-gw-name:nth-child(3n+1) { -webkit-animation-delay: .7s; animation-delay: .7s; }
.dz-gw-name:nth-child(3n+2) { -webkit-animation-delay: 1.05s; animation-delay: 1.05s; }
.dz-gw-step-art {
    width: 3.25rem;
    height: 3.25rem;
    object-fit: contain;
    flex-shrink: 0;
    border-radius: 0.85rem;
}
.dz-gw-ref-box {
    margin-top: 0.55rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.dz-gw-ref-box input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 0.55rem 0.7rem;
    font-size: 0.72rem;
    font-weight: 700;
    color: #334155;
    text-align: center;
}
.dz-gw-ref-box .dz-gw-copy {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.7rem;
    background: #6d28d9;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.45rem 0.85rem;
    align-self: flex-start;
}
.dz-gw-upload-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    justify-content: center;
    margin-top: 0.65rem;
}
.dz-gw-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.7rem;
    border: 1px solid #ddd6fe;
    background: #fff;
    color: #6d28d9;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.4rem 0.8rem;
}
.dz-gw-btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.7rem;
    border: 1px solid #fecdd3;
    background: #fff1f2;
    color: #be123c;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.4rem 0.8rem;
}
.dz-gw-btn-primary:disabled,
.dz-gw-btn-primary.is-disabled {
    opacity: .45;
    cursor: not-allowed;
    -webkit-animation: none !important;
    animation: none !important;
    transform: none !important;
    box-shadow: none !important;
}
.dz-gw-upload-status {
    margin-top: 0.45rem;
    text-align: center;
    font-size: 0.72rem;
    font-weight: 700;
    color: #6d28d9;
    min-height: 1rem;
}
.dz-google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    max-width: 360px;
    margin: 0 auto;
    min-height: 3.1rem;
    padding: 0.8rem 1.1rem;
    border-radius: 0.95rem;
    border: 1px solid #dadce0;
    background: #fff;
    color: #3c4043;
    font-weight: 700;
    box-shadow: 0 1px 2px rgba(60, 64, 67, 0.12);
    -webkit-animation: dz-gw-ctapulse 2.4s ease-in-out infinite;
    animation: dz-gw-ctapulse 2.4s ease-in-out infinite;
}
@-webkit-keyframes dz-gw-ctapulse {
    0%, 100% { -webkit-transform: translate3d(0,0,0); box-shadow: 0 1px 2px rgba(60,64,67,.12); }
    50% { -webkit-transform: translate3d(0,-2px,0); box-shadow: 0 8px 20px rgba(66,133,244,.22); }
}
@keyframes dz-gw-ctapulse {
    0%, 100% { transform: translate3d(0,0,0); box-shadow: 0 1px 2px rgba(60,64,67,.12); }
    50% { transform: translate3d(0,-2px,0); box-shadow: 0 8px 20px rgba(66,133,244,.22); }
}
.dz-google-btn__icon { display: grid; place-items: center; width: 1.5rem; height: 1.5rem; }
/* Compact inline upload inside step 2 */
.dz-gw-mini-up {
    margin-top: 0.55rem;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
}
.dz-gw-mini-up__thumb {
    width: 52px;
    height: 52px;
    border-radius: 0.7rem;
    object-fit: cover;
    border: 1.5px solid #ddd6fe;
    background: #faf5ff;
    flex-shrink: 0;
    display: none;
}
.dz-gw-mini-up.has-preview .dz-gw-mini-up__thumb { display: block; }
.dz-gw-mini-up__pick {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1.5px dashed #c4b5fd;
    background: #faf5ff;
    border-radius: 0.7rem;
    padding: 0.45rem 0.75rem;
    font-size: 0.72rem;
    font-weight: 800;
    color: #6d28d9;
    cursor: pointer;
}
.dz-gw-mini-up__pick input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.dz-gw-mini-up.is-drag .dz-gw-mini-up__pick {
    border-color: #7c3aed;
    background: #f5f3ff;
}
.dz-gw-mini-up__actions {
    display: none;
    align-items: center;
    gap: 0.35rem;
}
.dz-gw-mini-up.has-preview .dz-gw-mini-up__actions { display: inline-flex; }
.dz-gw-mini-up.has-preview .dz-gw-mini-up__pick-label { display: none; }
.dz-gw-mini-up__status {
    width: 100%;
    font-size: 0.68rem;
    font-weight: 700;
    color: #6d28d9;
    min-height: 0.9rem;
}
.dz-gw-submit-bar {
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px solid var(--line);
    display: grid;
    gap: 0.65rem;
}
.dz-gw-pending {
    text-align: center;
    padding: 1.15rem 1rem 1.25rem;
    border-radius: 1.2rem;
    border: 1px solid #fde68a;
    background: linear-gradient(180deg, #fffbeb 0%, #fff 70%);
    box-shadow: 0 10px 28px rgba(245, 158, 11, 0.1);
}
.dz-gw-pending__icon {
    width: 3.25rem;
    height: 3.25rem;
    margin: 0 auto 0.75rem;
    border-radius: 9999px;
    background: #fef3c7;
    color: #d97706;
    display: grid;
    place-items: center;
    -webkit-animation: dz-gw-pendingpulse 1.8s ease-in-out infinite;
    animation: dz-gw-pendingpulse 1.8s ease-in-out infinite;
}
@-webkit-keyframes dz-gw-pendingpulse {
    0%, 100% { -webkit-transform: scale(1); box-shadow: 0 0 0 0 rgba(245,158,11,.35); }
    50% { -webkit-transform: scale(1.06); box-shadow: 0 0 0 10px rgba(245,158,11,0); }
}
@keyframes dz-gw-pendingpulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245,158,11,.35); }
    50% { transform: scale(1.06); box-shadow: 0 0 0 10px rgba(245,158,11,0); }
}
.dz-gw-pending h2 {
    font-size: 1.05rem;
    font-weight: 900;
    color: #92400e;
}
.dz-gw-pending p {
    margin-top: 0.4rem;
    font-size: 0.85rem;
    color: #a16207;
    line-height: 1.45;
}
.dz-gw-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 360px;
    margin: 0 auto;
    border-radius: 0.95rem;
    background: #6d28d9;
    color: #fff;
    font-weight: 800;
    padding: 0.85rem 1.25rem;
    box-shadow: 0 10px 24px rgba(109, 40, 217, 0.22);
    -webkit-animation: dz-gw-ctapulse 2.4s ease-in-out infinite;
    animation: dz-gw-ctapulse 2.4s ease-in-out infinite;
}
.dz-gw-fb {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    border-radius: 0.7rem;
    background: #1877F2;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.4rem 0.65rem;
}
.dz-gw-fb img { width: 0.9rem; height: 0.9rem; border-radius: 0.15rem; }
.dz-gw-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.7rem;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.4rem 0.65rem;
    background: #fff;
}
.dz-gw-winner {
    position: relative;
    overflow: hidden;
    margin-bottom: 0.85rem;
    padding: 1.35rem 1.1rem 1.45rem;
    border-radius: 1.25rem;
    text-align: center;
    color: #fff;
    background:
        radial-gradient(120% 90% at 50% 0%, rgba(245, 215, 110, 0.28), transparent 55%),
        linear-gradient(135deg, #6d28d9 0%, #a21caf 45%, #db2777 100%);
    border: 1px solid rgba(255,255,255,0.22);
    box-shadow: 0 16px 36px rgba(109, 40, 217, 0.28);
}
.dz-gw-winner__eyebrow {
    margin: 0;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.85);
}
.dz-gw-winner__title {
    margin: 0.35rem 0 0;
    font-size: 1.35rem;
    font-weight: 900;
}
.dz-gw-winner__name {
    margin: 0.55rem 0 0;
    font-size: 1.7rem;
    font-weight: 900;
    color: #fde68a;
    text-shadow: 0 2px 12px rgba(0,0,0,0.25);
}
.dz-gw-winner__body {
    margin: 0.45rem auto 0;
    max-width: 26rem;
    font-size: 0.88rem;
    line-height: 1.45;
    color: rgba(255,255,255,0.9);
}
.dz-gw-winner__sparks {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.dz-gw-winner__sparks span {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #fde68a;
    box-shadow: 0 0 10px rgba(253, 230, 138, 0.85);
    animation: dzGwSparkle 2.4s ease-in-out infinite;
}
.dz-gw-winner__sparks span:nth-child(1) { top: 18%; left: 12%; animation-delay: 0s; }
.dz-gw-winner__sparks span:nth-child(2) { top: 28%; right: 14%; animation-delay: 0.35s; }
.dz-gw-winner__sparks span:nth-child(3) { bottom: 22%; left: 18%; animation-delay: 0.7s; }
.dz-gw-winner__sparks span:nth-child(4) { bottom: 18%; right: 20%; animation-delay: 1s; }
.dz-gw-winner__sparks span:nth-child(5) { top: 42%; left: 8%; animation-delay: 1.3s; width: 4px; height: 4px; }
.dz-gw-winner__sparks span:nth-child(6) { top: 16%; right: 28%; animation-delay: 1.6s; width: 5px; height: 5px; }
@keyframes dzGwSparkle {
    0%, 100% { opacity: 0.25; -webkit-transform: translate3d(0, 0, 0) scale(0.8); transform: translate3d(0, 0, 0) scale(0.8); }
    50% { opacity: 1; -webkit-transform: translate3d(0, -6px, 0) scale(1.2); transform: translate3d(0, -6px, 0) scale(1.2); }
}
.dz-gw-announced {
    margin-bottom: 0.85rem;
    padding: 1.2rem 1.1rem 1.3rem;
    border-radius: 1.25rem;
    text-align: center;
    color: #1e1b4b;
    background:
        radial-gradient(120% 90% at 50% 0%, rgba(245, 215, 110, 0.35), transparent 55%),
        linear-gradient(135deg, #fdf4ff 0%, #ede9fe 55%, #fce7f3 100%);
    border: 1px solid rgba(109, 40, 217, 0.16);
    box-shadow: 0 12px 28px rgba(109, 40, 217, 0.12);
}
.dz-gw-announced__eyebrow {
    margin: 0;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #7c3aed;
}
.dz-gw-announced__title {
    margin: 0.35rem 0 0;
    font-size: 1.2rem;
    font-weight: 900;
    color: #312e81;
}
.dz-gw-announced__name {
    margin: 0.55rem 0 0;
    font-size: 1.55rem;
    font-weight: 900;
    color: #a21caf;
}
.dz-gw-announced__body {
    margin: 0.45rem auto 0;
    max-width: 26rem;
    font-size: 0.88rem;
    line-height: 1.45;
    color: #4c1d95;
}
.dz-gw-announced__soon {
    margin: 0.85rem auto 0;
    max-width: 26rem;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.45;
    color: #312e81;
}
.dz-gw-announced__fb {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 0.9rem;
    padding: 0.7rem 1.15rem;
    border-radius: 0.85rem;
    background: #1877F2;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 800;
    box-shadow: 0 8px 18px rgba(24, 119, 242, 0.28);
}
.dz-gw-announced__fb img {
    width: 1rem;
    height: 1rem;
    border-radius: 0.15rem;
}

/* Keep light motion even when iOS “Reduce Motion” is on */
@media (prefers-reduced-motion: reduce) {
    .dz-gw-floater { -webkit-animation-duration: 14s !important; animation-duration: 14s !important; }
    .dz-gw-winner__sparks span { animation: none !important; opacity: 0.7; }
    .dz-gw-hero__bg { -webkit-animation-duration: 24s !important; animation-duration: 24s !important; }
    .dz-gw-hero__shine { -webkit-animation-duration: 8s !important; animation-duration: 8s !important; }
    .dz-gw-count__cell,
    .dz-gw-step-row__num,
    .dz-gw-step-row__icon,
    .dz-gw-name,
    .dz-gw-step-row,
    .dz-google-btn,
    .dz-gw-btn-primary { -webkit-animation-duration: 5s !important; animation-duration: 5s !important; }
    .dz-gw-panel,
    .dz-gw-hero { -webkit-animation: none !important; animation: none !important; }
}
</style>
@endpush

@section('content')
@php
    $fbIcon = \App\Models\Giveaway::assetUrl('facebook.svg');
    $tagsIcon = \App\Models\Giveaway::assetUrl('tags.svg');
    $shareIcon = \App\Models\Giveaway::assetUrl('share.svg');
    $endsAtMs = $giveaway?->ends_at ? ((int) $giveaway->ends_at->getTimestamp() * 1000) : null;
    $startsAtMs = $giveaway?->starts_at ? ((int) $giveaway->starts_at->getTimestamp() * 1000) : null;
    $entryEditable = $entry?->isEditable() ?? false;
    $entrySubmitted = $entry?->isSubmitted() ?? false;
    $isPending = ($entry?->status === 'pending');
    $isApproved = ($entry?->status === 'approved');
    $hasScreenshot = $entry?->hasScreenshot() ?? false;
    $screenshotUrl = $hasScreenshot ? ($entry->screenshotPublicUrl().'?t='.$entry->updated_at?->getTimestamp()) : null;
    $statusKey = match ($entry?->status) {
        'approved' => 'status_approved',
        'declined' => 'status_declined',
        'pending' => 'status_pending',
        'draft' => 'status_draft',
        default => null,
    };
    $statusClass = match ($entry?->status) {
        'approved' => 'bg-emerald-100 text-emerald-800',
        'declined' => 'bg-rose-100 text-rose-800',
        'pending' => 'bg-amber-100 text-amber-800',
        'draft' => 'bg-slate-100 text-slate-700',
        default => 'bg-slate-100 text-slate-700',
    };
    $enrolleeNames = $enrollees->map(fn ($row) => $row->displayFirstName())->values();
    $giveawayOpen = $giveaway?->isCurrentlyActive() ?? false;
@endphp

<div class="dz-gw dz-gw-shell py-5 sm:py-8">
    <div class="dz-gw-floaters" aria-hidden="true">
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
    </div>

    <div class="container mx-auto px-4">
        <div class="dz-gw-wrap space-y-3.5 sm:space-y-4">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 text-center">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 text-center">{{ session('error') }}</div>
            @endif

            @unless($giveaway)
                <div class="dz-gw-panel py-14">
                    <img src="{{ $shareIcon }}" alt="" class="mx-auto w-14 h-14 mb-3">
                    <h1 class="text-2xl font-black text-slate-900">{{ __('giveaway.no_active') }}</h1>
                </div>
            @else
                @if($giveawayOpen)
                <section class="dz-gw-hero">
                    <img src="{{ $giveaway->heroImageUrl() }}" alt="" class="dz-gw-hero__bg" loading="eager">
                    <div class="dz-gw-hero__overlay"></div>
                    <div class="dz-gw-hero__shine" aria-hidden="true"></div>
                    <div class="dz-gw-hero__content">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-violet-200 mb-1.5">{{ __('giveaway.eyebrow') }}</p>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight max-w-xl">{{ $giveaway->title }}</h1>
                        <p class="mt-2 text-sm text-slate-200 max-w-md leading-relaxed">
                            {{ $giveaway->description ?: __('giveaway.subtitle') }}
                        </p>
                        <div class="mt-4 w-full flex flex-col items-center">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-violet-100/85 mb-1.5">{{ __('giveaway.countdown_label') }}</p>
                            <div class="dz-gw-count" id="gw-countdown" data-ends="{{ $endsAtMs }}" data-starts="{{ $startsAtMs }}">
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="d">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.days') }}</span></div>
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="h">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.hours') }}</span></div>
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="m">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.mins') }}</span></div>
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="s">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.secs') }}</span></div>
                            </div>
                            <p id="gw-countdown-note" class="mt-1.5 text-sm font-semibold text-violet-100/90 hidden"></p>
                        </div>
                    </div>
                </section>

                @if($isPending)
                    <section class="dz-gw-pending" aria-live="polite">
                        <div class="dz-gw-pending__icon" aria-hidden="true">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="dz-gw-chip bg-amber-100 text-amber-800">{{ __('giveaway.status_pending') }}</span>
                        <h2 class="mt-2">{{ __('giveaway.pending_title') }}</h2>
                        <p>{{ __('giveaway.pending_body') }}</p>
                    </section>

                    <section class="dz-gw-panel !text-left">
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">3</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $shareIcon }}" alt="" class="dz-gw-step-row__icon">
                                    <h3>{{ __('giveaway.step3_title') }}</h3>
                                </div>
                                <p>{{ __('giveaway.step3_desc') }}</p>
                                @if($entry)
                                    <div class="dz-gw-ref-box">
                                        <input id="gw-ref-link-step" type="text" readonly value="{{ $entry->referralUrl() }}">
                                        <button type="button" class="dz-gw-copy" data-gw-copy="gw-ref-link-step">{{ __('giveaway.copy_link') }}</button>
                                        <p class="text-xs font-bold {{ $entry->referralSatisfied() ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ __('giveaway.referral_progress', ['count' => $entry->referralSatisfied() ? 1 : 0]) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    </section>
                @elseif($isApproved)
                    <section class="dz-gw-panel space-y-3">
                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-b from-emerald-50 to-white px-4 py-5 text-center">
                            <span class="dz-gw-chip bg-emerald-100 text-emerald-800 mb-2">{{ __('giveaway.status_approved') }}</span>
                            <h2 class="text-lg font-black text-emerald-900">{{ __('giveaway.approved_title') }}</h2>
                            <p class="mt-2 text-sm text-emerald-800/90 max-w-md mx-auto leading-relaxed">{{ __('giveaway.approved_body') }}</p>
                        </div>
                        @if($entry)
                            <article class="dz-gw-step-row !mt-1">
                                <span class="dz-gw-step-row__num">3</span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $shareIcon }}" alt="" class="dz-gw-step-row__icon">
                                        <h3>{{ __('giveaway.step3_title') }}</h3>
                                    </div>
                                    <div class="dz-gw-ref-box">
                                        <input id="gw-ref-link-step" type="text" readonly value="{{ $entry->referralUrl() }}">
                                        <button type="button" class="dz-gw-copy" data-gw-copy="gw-ref-link-step">{{ __('giveaway.copy_link') }}</button>
                                        <p class="text-xs font-bold {{ $entry->referralSatisfied() ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ __('giveaway.referral_progress', ['count' => $entry->referralSatisfied() ? 1 : 0]) }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endif
                    </section>
                @else
                <section class="dz-gw-panel !text-left">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h2 class="text-base font-black text-slate-900">{{ __('giveaway.how_title') }}</h2>
                        <span class="dz-gw-chip bg-violet-50 text-violet-700">3 {{ __('giveaway.steps_short') }}</span>
                    </div>

                    @if($entryEditable && $giveaway->isCurrentlyActive())
                        @if($entry->status === 'declined' && $entry->decline_reason)
                            <div class="mb-2.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs text-rose-900">
                                <p class="font-bold mb-0.5">{{ __('giveaway.decline_reason') }}</p>
                                <p>{{ $entry->decline_reason }}</p>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('giveaway.enter', $giveaway->slug) }}" id="gw-entry-form"
                              data-upload-url="{{ route('giveaway.screenshot.upload', $giveaway->slug) }}"
                              data-delete-url="{{ route('giveaway.screenshot.delete', $giveaway->slug) }}"
                              data-has-screenshot="{{ $hasScreenshot ? '1' : '0' }}"
                              data-referral-satisfied="{{ ($entry?->referralSatisfied() ?? false) ? '1' : '0' }}">
                            @csrf
                    @endif

                    <div class="dz-gw-steps">
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">1</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $fbIcon }}" alt="" class="dz-gw-step-row__icon">
                                    <h3>{{ __('giveaway.step1_title') }}</h3>
                                </div>
                                <p>{{ __('giveaway.step1_desc') }}</p>
                                <div class="dz-gw-step-actions">
                                    <a href="{{ $giveaway->facebook_post_url }}" target="_blank" rel="noopener noreferrer" class="dz-gw-fb">
                                        <img src="{{ $fbIcon }}" alt=""> {{ __('giveaway.open_post') }}
                                    </a>
                                    <a href="{{ $giveaway->facebook_page_url }}" target="_blank" rel="noopener noreferrer" class="dz-gw-ghost">
                                        {{ __('giveaway.open_page') }}
                                    </a>
                                </div>
                            </div>
                        </article>
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">2</span>
                            <div class="min-w-0 flex items-start gap-3">
                                <img src="{{ $tagsIcon }}" alt="" class="dz-gw-step-art" width="52" height="52">
                                <div class="min-w-0 flex-1">
                                    <h3>{{ __('giveaway.step2_title') }}</h3>
                                    <p>{{ __('giveaway.step2_desc') }}</p>
                                    @if($entryEditable && $giveaway->isCurrentlyActive())
                                        <div class="dz-gw-mini-up {{ $hasScreenshot ? 'has-preview' : '' }}" id="gw-upload">
                                            <img src="{{ $screenshotUrl }}" alt="" class="dz-gw-mini-up__thumb" id="gw-preview">
                                            <label class="dz-gw-mini-up__pick">
                                                <input type="file" id="gw-screenshot" accept="image/jpeg,image/png,image/webp,image/*">
                                                <span class="dz-gw-mini-up__pick-label">{{ __('giveaway.upload_cta') }}</span>
                                                <span class="hidden has-preview-label">{{ __('giveaway.replace_screenshot') }}</span>
                                            </label>
                                            <div class="dz-gw-mini-up__actions" id="gw-upload-actions">
                                                <label for="gw-screenshot" class="dz-gw-btn-secondary cursor-pointer">{{ __('giveaway.replace_screenshot') }}</label>
                                                <button type="button" class="dz-gw-btn-danger" id="gw-delete-shot">{{ __('giveaway.delete_screenshot') }}</button>
                                            </div>
                                            <p class="dz-gw-mini-up__status" id="gw-upload-status"></p>
                                        </div>
                                    @elseif(auth()->guest())
                                        <p class="mt-2 text-xs font-semibold text-violet-700">{{ __('giveaway.step2_login_hint') }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">3</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $shareIcon }}" alt="" class="dz-gw-step-row__icon">
                                    <h3>{{ __('giveaway.step3_title') }}</h3>
                                </div>
                                <p>{{ __('giveaway.step3_desc') }}</p>
                                @auth
                                    @if($entry)
                                        <div class="dz-gw-ref-box">
                                            <input id="gw-ref-link-step" type="text" readonly value="{{ $entry->referralUrl() }}">
                                            <button type="button" class="dz-gw-copy" data-gw-copy="gw-ref-link-step">{{ __('giveaway.copy_link') }}</button>
                                            <p class="text-xs font-bold {{ $entry->referralSatisfied() ? 'text-emerald-700' : 'text-amber-700' }}">
                                                {{ __('giveaway.referral_progress', ['count' => $entry->referralSatisfied() ? 1 : 0]) }}
                                            </p>
                                        </div>
                                    @endif
                                @else
                                    <p class="mt-2 text-xs font-semibold text-violet-700">{{ __('giveaway.step3_login_hint') }}</p>
                                @endauth
                            </div>
                        </article>
                    </div>

                    @if($entryEditable && $giveaway->isCurrentlyActive())
                            <div class="dz-gw-submit-bar">
                                <label class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-700">
                                    <input type="checkbox" name="liked_followed_ack" id="gw-ack" value="1" class="mt-0.5 rounded border-slate-300 text-violet-700 focus:ring-violet-600" {{ old('liked_followed_ack', $entry->liked_followed_ack) ? 'checked' : '' }}>
                                    <span>{{ __('giveaway.ack_label') }}</span>
                                </label>
                                @error('liked_followed_ack')
                                    <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                                <button type="submit" id="gw-submit" class="dz-gw-btn-primary !max-w-none" disabled>{{ __('giveaway.submit') }}</button>
                                <p class="text-xs text-slate-500 text-center" id="gw-submit-hint">{{ __('giveaway.submit_locked_hint') }}</p>
                            </div>
                        </form>
                    @endif

                    @if($giveaway->instructions)
                        <p class="mt-3 text-xs text-slate-500 whitespace-pre-line leading-relaxed">{{ $giveaway->instructions }}</p>
                    @endif
                </section>
                @endif
                @endif {{-- $giveawayOpen: hide hero + how-to-join when ended --}}

                @if(!empty($winnerReveal))
                    <section class="dz-gw-winner" data-gw-winner aria-live="polite">
                        <div class="dz-gw-winner__sparks" aria-hidden="true">
                            <span></span><span></span><span></span><span></span><span></span><span></span>
                        </div>
                        <p class="dz-gw-winner__eyebrow">{{ __('giveaway.winner_eyebrow') }}</p>
                        <h2 class="dz-gw-winner__title">{{ __('giveaway.winner_title') }}</h2>
                        <p class="dz-gw-winner__name">{{ $winnerReveal['name'] }}</p>
                        <p class="dz-gw-winner__body">{{ __('giveaway.winner_body') }}</p>
                    </section>
                @endif

                @if(!empty($winnerAnnouncement))
                    <section class="dz-gw-announced" data-gw-announced aria-live="polite">
                        <p class="dz-gw-announced__eyebrow">{{ __('giveaway.announced_eyebrow') }}</p>
                        <h2 class="dz-gw-announced__title">{{ __('giveaway.announced_title') }}</h2>
                        <p class="dz-gw-announced__name">{{ $winnerAnnouncement['name'] }}</p>
                        <p class="dz-gw-announced__body">{{ __('giveaway.announced_body') }}</p>
                        <p class="dz-gw-announced__soon">{{ __('giveaway.more_giveaways_soon') }}</p>
                        @if($giveaway->facebook_page_url)
                            <a href="{{ $giveaway->facebook_page_url }}" target="_blank" rel="noopener noreferrer" class="dz-gw-announced__fb">
                                <img src="{{ $fbIcon }}" alt="" width="16" height="16">
                                {{ __('giveaway.follow_facebook') }}
                            </a>
                        @endif
                    </section>
                @endif

                @if($giveawayOpen || !empty($canSpinGiveaway))
                <section class="dz-gw-panel dz-gw-enrolled">
                    <div class="flex items-center justify-center gap-2 mb-1">
                        <h2 class="text-sm font-black text-slate-900">{{ __('giveaway.enrolled_label') }}</h2>
                        <span class="dz-gw-chip bg-violet-100 text-violet-800">{{ number_format($enrolleeCount) }}</span>
                    </div>
                    <div class="dz-gw-enrolled__grid" aria-label="{{ __('giveaway.enrolled_label') }}">
                        @forelse($enrolleeNames as $name)
                            <span class="dz-gw-name">{{ $name }}</span>
                        @empty
                            <span class="dz-gw-name is-empty">{{ __('giveaway.enrolled_empty') }}</span>
                        @endforelse
                    </div>
                </section>
                @endif

                @if(!empty($canSpinGiveaway) && $giveaway)
                    @include('components.giveaway-spin-wheel', [
                        'giveawayParticipants' => $giveawayParticipants ?? [],
                        'spinUrl' => route('giveaway.spin', $giveaway->slug),
                        'drawLiveEnabled' => $drawLiveEnabled ?? false,
                        'giveawayTitle' => $giveaway->title,
                    ])
                @endif

                @if($giveawayOpen)
                @guest
                    <section class="dz-gw-panel">
                        <h2 class="text-lg font-black text-slate-900">{{ __('giveaway.login_title') }}</h2>
                        <p class="mt-1.5 text-sm text-slate-500 max-w-sm mx-auto">{{ __('giveaway.login_subtitle') }}</p>
                        <div class="mt-4">
                            @include('components.google-sign-in-button', [
                                'href' => route('auth.google', ['redirect' => '/giveaway/'.$giveaway->slug]),
                            ])
                        </div>
                    </section>
                @elseif($entry?->status === 'declined')
                    <section class="dz-gw-panel space-y-3">
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-center" role="alert">
                            <span class="dz-gw-chip bg-rose-100 text-rose-800 mb-2">{{ __('giveaway.status_declined') }}</span>
                            <h2 class="text-base font-black text-rose-900">{{ __('giveaway.declined_title') }}</h2>
                            <p class="mt-2 text-sm text-rose-800 leading-relaxed">{{ __('giveaway.declined_body') }}</p>
                            @if($entry->decline_reason)
                                <p class="mt-3 rounded-xl bg-white/80 border border-rose-100 px-3 py-2 text-sm text-rose-900 font-semibold">
                                    {{ $entry->decline_reason }}
                                </p>
                            @endif
                            <p class="mt-3 text-xs font-bold text-rose-700">{{ __('giveaway.declined_fix_hint') }}</p>
                        </div>
                    </section>
                @endguest
                @endif
            @endunless
        </div>
    </div>
</div>
@endsection

@if($giveaway)
@push('scripts')
<script>
(function () {
    const root = document.getElementById('gw-countdown');
    if (root) {
        const ends = parseInt(root.dataset.ends || '0', 10);
        const starts = parseInt(root.dataset.starts || '0', 10);
        const note = document.getElementById('gw-countdown-note');
        const endedLabel = @json(__('giveaway.ended'));
        const soonLabel = @json(__('giveaway.not_started'));

        function tick() {
            const now = Date.now();
            let target = ends;
            let label = null;
            if (starts && now < starts) {
                target = starts;
                label = soonLabel;
            } else if (ends && now >= ends) {
                ['d','h','m','s'].forEach((u) => {
                    const el = root.querySelector('[data-unit="'+u+'"]');
                    if (el) el.textContent = '0';
                });
                if (note) { note.textContent = endedLabel; note.classList.remove('hidden'); }
                return;
            }
            if (note) {
                if (label) { note.textContent = label; note.classList.remove('hidden'); }
                else note.classList.add('hidden');
            }
            let diff = Math.max(0, target - now);
            const d = Math.floor(diff / 86400000); diff -= d * 86400000;
            const h = Math.floor(diff / 3600000); diff -= h * 3600000;
            const m = Math.floor(diff / 60000); diff -= m * 60000;
            const s = Math.floor(diff / 1000);
            const map = { d, h, m, s };
            Object.keys(map).forEach((u) => {
                const el = root.querySelector('[data-unit="'+u+'"]');
                if (el) el.textContent = String(map[u]);
            });
        }
        tick();
        setInterval(tick, 1000);
    }

    const copiedLabel = @json(__('giveaway.copied'));
    const copyLabel = @json(__('giveaway.copy_link'));
    document.querySelectorAll('[data-gw-copy]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const input = document.getElementById(btn.getAttribute('data-gw-copy'));
            if (!input) return;
            try {
                await navigator.clipboard.writeText(input.value);
                btn.textContent = copiedLabel;
                setTimeout(function () { btn.textContent = copyLabel; }, 1600);
            } catch (e) {
                input.select();
                document.execCommand('copy');
                btn.textContent = copiedLabel;
                setTimeout(function () { btn.textContent = copyLabel; }, 1600);
            }
        });
    });

    const form = document.getElementById('gw-entry-form');
    const upload = document.getElementById('gw-upload');
    const input = document.getElementById('gw-screenshot');
    const preview = document.getElementById('gw-preview');
    const statusEl = document.getElementById('gw-upload-status');
    const ack = document.getElementById('gw-ack');
    const submitBtn = document.getElementById('gw-submit');
    const submitHint = document.getElementById('gw-submit-hint');
    const deleteBtn = document.getElementById('gw-delete-shot');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || form?.querySelector('input[name="_token"]')?.value
        || '';

    let hasScreenshot = form?.dataset.hasScreenshot === '1';
    let referralSatisfied = form?.dataset.referralSatisfied === '1';

    function syncSubmit() {
        if (!submitBtn) return;
        const ready = hasScreenshot && referralSatisfied && !!(ack && ack.checked);
        submitBtn.disabled = !ready;
        submitBtn.classList.toggle('is-disabled', !ready);
        if (submitHint) submitHint.classList.toggle('hidden', ready);
    }

    function setPreview(url) {
        if (!upload || !preview) return;
        if (url) {
            preview.src = url;
            upload.classList.add('has-preview');
            hasScreenshot = true;
        } else {
            preview.removeAttribute('src');
            upload.classList.remove('has-preview');
            hasScreenshot = false;
        }
        if (form) form.dataset.hasScreenshot = hasScreenshot ? '1' : '0';
        syncSubmit();
    }

    async function uploadFile(file) {
        if (!form || !file) return;
        statusEl && (statusEl.textContent = @json(__('giveaway.uploading')));
        const body = new FormData();
        body.append('screenshot', file);
        body.append('_token', csrf);
        try {
            const res = await fetch(form.dataset.uploadUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: body,
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'upload failed');
            if (typeof data.referral_satisfied === 'boolean') {
                referralSatisfied = data.referral_satisfied;
                if (form) form.dataset.referralSatisfied = referralSatisfied ? '1' : '0';
            }
            setPreview(data.url);
            statusEl && (statusEl.textContent = data.message || @json(__('giveaway.screenshot_saved')));
        } catch (err) {
            statusEl && (statusEl.textContent = err.message || 'Upload failed');
            setPreview(hasScreenshot ? preview.src : null);
        }
        if (input) input.value = '';
    }

    if (input) {
        input.addEventListener('change', function () {
            if (input.files && input.files[0]) uploadFile(input.files[0]);
        });
    }
    if (upload) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            upload.addEventListener(evt, function (e) { e.preventDefault(); upload.classList.add('is-drag'); });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            upload.addEventListener(evt, function (e) { e.preventDefault(); upload.classList.remove('is-drag'); });
        });
        upload.addEventListener('drop', function (e) {
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) uploadFile(file);
        });
    }
    if (deleteBtn && form) {
        deleteBtn.addEventListener('click', async function () {
            statusEl && (statusEl.textContent = @json(__('giveaway.uploading')));
            try {
                const res = await fetch(form.dataset.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'delete failed');
                setPreview(null);
                statusEl && (statusEl.textContent = data.message || @json(__('giveaway.screenshot_removed')));
            } catch (err) {
                statusEl && (statusEl.textContent = err.message || 'Delete failed');
            }
        });
    }
    if (ack) ack.addEventListener('change', syncSubmit);
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!hasScreenshot || !referralSatisfied || !(ack && ack.checked)) {
                e.preventDefault();
                syncSubmit();
            }
        });
    }
    syncSubmit();
})();
</script>
@endpush
@endif
