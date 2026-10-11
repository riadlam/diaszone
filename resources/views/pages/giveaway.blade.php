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
    overflow: hidden;
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
/* Enrolled — soft breathe grid (more room, no marquee) */
.dz-gw-enrolled {
    padding: 0.85rem 0.35rem 0.35rem;
}
.dz-gw-enrolled__grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.65rem 0.7rem;
    padding: 0.85rem 0.5rem 1rem;
    min-height: 3.5rem;
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
    0%, 100% { -webkit-transform: translate3d(0, 0, 0) scale(1); box-shadow: 0 0 0 rgba(109,40,217,0); }
    50% { -webkit-transform: translate3d(0, -3px, 0) scale(1.03); box-shadow: 0 6px 14px rgba(109,40,217,.1); }
}
@keyframes dz-gw-breathe {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); box-shadow: 0 0 0 rgba(109,40,217,0); }
    50% { transform: translate3d(0, -3px, 0) scale(1.03); box-shadow: 0 6px 14px rgba(109,40,217,.1); }
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
.dz-gw-upload {
    position: relative;
    border: 1.5px dashed #c4b5fd;
    background: linear-gradient(180deg, #faf5ff 0%, #fff 100%);
    border-radius: 1.05rem;
    padding: 0.95rem;
    cursor: pointer;
    text-align: center;
}
.dz-gw-upload.is-drag {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}
.dz-gw-upload input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.dz-gw-upload__preview {
    display: none;
    margin: 0 auto 0.65rem;
    width: 104px;
    height: 104px;
    border-radius: 0.85rem;
    object-fit: cover;
    border: 2px solid #ddd6fe;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
}
.dz-gw-upload.has-preview .dz-gw-upload__preview { display: block; }
.dz-gw-upload.has-preview .dz-gw-upload__placeholder { display: none; }
.dz-gw-upload__icon {
    width: 2.5rem;
    height: 2.5rem;
    margin: 0 auto 0.45rem;
    border-radius: 0.8rem;
    background: #ede9fe;
    color: #6d28d9;
    display: grid;
    place-items: center;
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
/* Keep light motion even when iOS “Reduce Motion” is on */
@media (prefers-reduced-motion: reduce) {
    .dz-gw-floater { -webkit-animation-duration: 14s !important; animation-duration: 14s !important; }
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

                <section class="dz-gw-panel !text-left">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h2 class="text-base font-black text-slate-900">{{ __('giveaway.how_title') }}</h2>
                        <span class="dz-gw-chip bg-violet-50 text-violet-700">3 {{ __('giveaway.steps_short') }}</span>
                    </div>
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
                                <div class="min-w-0">
                                    <h3>{{ __('giveaway.step2_title') }}</h3>
                                    <p>{{ __('giveaway.step2_desc') }}</p>
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
                    @if($giveaway->instructions)
                        <p class="mt-3 text-xs text-slate-500 whitespace-pre-line leading-relaxed">{{ $giveaway->instructions }}</p>
                    @endif
                </section>

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
                @else
                    @if($entrySubmitted && ! $entryEditable)
                        <section class="dz-gw-panel space-y-3.5">
                            <div class="flex flex-col items-center gap-2">
                                <h2 class="text-base font-black text-slate-900">{{ __('giveaway.status_title') }}</h2>
                                <span class="dz-gw-chip {{ $statusClass }}">{{ __('giveaway.'.$statusKey) }}</span>
                            </div>
                            @if($entry->status === 'declined' && $entry->decline_reason)
                                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-3 text-sm text-rose-900 text-left">
                                    <p class="font-bold mb-0.5">{{ __('giveaway.decline_reason') }}</p>
                                    <p>{{ $entry->decline_reason }}</p>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500 mb-1.5">{{ __('giveaway.referral_title') }}</h3>
                                <p class="text-sm text-slate-500 mb-2.5 max-w-md mx-auto">{{ __('giveaway.referral_hint') }}</p>
                                <div class="flex flex-col gap-2 max-w-md mx-auto">
                                    <input id="gw-ref-link" type="text" readonly value="{{ $entry->referralUrl() }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 text-center">
                                    <button type="button" class="dz-gw-btn-primary !shadow-none !max-w-none" data-gw-copy="gw-ref-link">
                                        {{ __('giveaway.copy_link') }}
                                    </button>
                                </div>
                                <p class="mt-2.5 text-sm font-bold {{ $entry->referralSatisfied() ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ __('giveaway.referral_progress', ['count' => $entry->referralSatisfied() ? 1 : 0]) }}
                                </p>
                            </div>
                        </section>
                    @elseif($giveaway->isCurrentlyActive() && $entryEditable)
                        <section class="dz-gw-panel">
                            <div class="flex flex-col items-center gap-2 mb-3">
                                <h2 class="text-base font-black text-slate-900">{{ __('giveaway.form_title') }}</h2>
                                @if($entry->status === 'declined')
                                    <span class="dz-gw-chip {{ $statusClass }}">{{ __('giveaway.status_declined') }}</span>
                                    @if($entry->decline_reason)
                                        <div class="w-full rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-3 text-sm text-rose-900 text-left">
                                            <p class="font-bold mb-0.5">{{ __('giveaway.decline_reason') }}</p>
                                            <p>{{ $entry->decline_reason }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <form method="POST" action="{{ route('giveaway.enter', $giveaway->slug) }}" id="gw-entry-form" class="space-y-3.5 text-left max-w-md mx-auto"
                                  data-upload-url="{{ route('giveaway.screenshot.upload', $giveaway->slug) }}"
                                  data-delete-url="{{ route('giveaway.screenshot.delete', $giveaway->slug) }}"
                                  data-has-screenshot="{{ $hasScreenshot ? '1' : '0' }}">
                                @csrf
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5 text-center">{{ __('giveaway.screenshot_label') }}</label>
                                    <label class="dz-gw-upload block {{ $hasScreenshot ? 'has-preview' : '' }}" id="gw-upload">
                                        <input type="file" id="gw-screenshot" accept="image/jpeg,image/png,image/webp,image/*">
                                        <img src="{{ $screenshotUrl }}" alt="" class="dz-gw-upload__preview" id="gw-preview">
                                        <div class="dz-gw-upload__placeholder">
                                            <div class="dz-gw-upload__icon" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p class="text-sm font-bold text-violet-800">{{ __('giveaway.upload_cta') }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ __('giveaway.screenshot_hint') }}</p>
                                        </div>
                                    </label>
                                    <div class="dz-gw-upload-actions {{ $hasScreenshot ? '' : 'hidden' }}" id="gw-upload-actions">
                                        <label for="gw-screenshot" class="dz-gw-btn-secondary cursor-pointer">{{ __('giveaway.replace_screenshot') }}</label>
                                        <button type="button" class="dz-gw-btn-danger" id="gw-delete-shot">{{ __('giveaway.delete_screenshot') }}</button>
                                    </div>
                                    <p class="dz-gw-upload-status" id="gw-upload-status"></p>
                                    @error('screenshot')
                                        <p class="mt-1.5 text-xs font-semibold text-rose-600 text-center">{{ $message }}</p>
                                    @enderror
                                </div>
                                <label class="flex items-start gap-3 text-sm text-slate-700">
                                    <input type="checkbox" name="liked_followed_ack" id="gw-ack" value="1" class="mt-1 rounded border-slate-300 text-violet-700 focus:ring-violet-600" {{ old('liked_followed_ack', $entry->liked_followed_ack) ? 'checked' : '' }}>
                                    <span>{{ __('giveaway.ack_label') }}</span>
                                </label>
                                @error('liked_followed_ack')
                                    <p class="text-xs font-semibold text-rose-600 text-center">{{ $message }}</p>
                                @enderror
                                <div class="pt-0.5 text-center">
                                    <button type="submit" id="gw-submit" class="dz-gw-btn-primary" disabled>{{ __('giveaway.submit') }}</button>
                                    <p class="mt-2 text-xs text-slate-500" id="gw-submit-hint">{{ __('giveaway.submit_locked_hint') }}</p>
                                </div>
                            </form>
                        </section>
                    @else
                        <section class="dz-gw-panel">
                            <p class="font-bold text-slate-700">{{ __('giveaway.closed') }}</p>
                        </section>
                    @endif
                @endguest
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
    const actions = document.getElementById('gw-upload-actions');
    const statusEl = document.getElementById('gw-upload-status');
    const ack = document.getElementById('gw-ack');
    const submitBtn = document.getElementById('gw-submit');
    const submitHint = document.getElementById('gw-submit-hint');
    const deleteBtn = document.getElementById('gw-delete-shot');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || form?.querySelector('input[name="_token"]')?.value
        || '';

    let hasScreenshot = form?.dataset.hasScreenshot === '1';

    function syncSubmit() {
        if (!submitBtn) return;
        const ready = hasScreenshot && !!(ack && ack.checked);
        submitBtn.disabled = !ready;
        submitBtn.classList.toggle('is-disabled', !ready);
        if (submitHint) submitHint.classList.toggle('hidden', ready);
    }

    function setPreview(url) {
        if (!upload || !preview) return;
        if (url) {
            preview.src = url;
            upload.classList.add('has-preview');
            if (actions) actions.classList.remove('hidden');
            hasScreenshot = true;
        } else {
            preview.removeAttribute('src');
            upload.classList.remove('has-preview');
            if (actions) actions.classList.add('hidden');
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
            if (!hasScreenshot || !(ack && ack.checked)) {
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
