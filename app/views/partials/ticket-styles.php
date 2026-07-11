<?php /** Self-contained styles for the event ticket — include once per page/fragment. */ ?>
<style>
.eticket{--tk-ink:#07140E;--tk-ink2:#0B1E16;--tk-ink3:#102B20;--tk-gold:#C9A227;--tk-gold2:#E4C865;--tk-green:#0C7A4D;--tk-sage:#9FB3A8;
  display:flex;max-width:640px;margin:0 auto;background:linear-gradient(135deg,#0B1E16,#102B20);
  border:1px solid rgba(201,162,39,.35);border-radius:12px;overflow:hidden;color:#E8EFE9;
  font-family:"Plus Jakarta Sans",system-ui,sans-serif;position:relative;box-shadow:0 24px 60px -30px rgba(0,0,0,.6)}
.eticket *{box-sizing:border-box}
.eticket-main{flex:1;padding:26px 28px;position:relative;min-width:0}
.eticket-brand{display:flex;align-items:center;gap:11px;margin-bottom:20px}
.eticket-brand svg{width:34px;height:34px;flex:0 0 34px}
.eticket-brand b{font-family:"Fraunces",Georgia,serif;font-size:15px;font-weight:600;display:block;line-height:1.15}
.eticket-brand span{font-family:"IBM Plex Mono",monospace;font-size:8.5px;letter-spacing:.18em;color:var(--tk-sage);text-transform:uppercase}
.eticket-type{display:inline-block;font-family:"IBM Plex Mono",monospace;font-size:10px;letter-spacing:.14em;text-transform:uppercase;
  color:#1a1405;background:var(--tk-gold);padding:5px 11px;border-radius:30px;font-weight:600;margin-bottom:16px}
.eticket-holder .lab{font-family:"IBM Plex Mono",monospace;font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--tk-sage)}
.eticket-holder .name{font-family:"Fraunces",Georgia,serif;font-size:26px;color:#fff;font-weight:600;line-height:1.1;margin-top:4px}
.eticket-meta{display:flex;flex-wrap:wrap;gap:20px;margin-top:20px}
.eticket-meta .lab{font-family:"IBM Plex Mono",monospace;font-size:9.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--tk-sage)}
.eticket-meta .val{font-family:"Fraunces",Georgia,serif;font-size:14.5px;color:#E8EFE9;margin-top:3px}
.eticket-glow{position:absolute;right:-30%;top:-40%;width:60%;height:160%;background:radial-gradient(circle,rgba(201,162,39,.16),transparent 60%);pointer-events:none}
.eticket-stub{width:206px;flex:0 0 206px;padding:24px 20px;display:flex;flex-direction:column;align-items:center;justify-content:center;
  background:rgba(0,0,0,.18);border-left:2px dashed rgba(159,179,168,.4);position:relative;text-align:center}
/* perforation notches */
.eticket-stub::before,.eticket-stub::after{content:"";position:absolute;left:-9px;width:16px;height:16px;border-radius:50%;background:#07140E}
.eticket-stub::before{top:-9px}.eticket-stub::after{bottom:-9px}
.eticket-qr{width:130px;height:130px;background:#fff;border-radius:8px;padding:9px}
.eticket-qr svg,.eticket-qr img{width:100%;height:100%;display:block}
.eticket-code{font-family:"IBM Plex Mono",monospace;font-size:13px;letter-spacing:.06em;color:#fff;margin-top:14px;
  background:rgba(255,255,255,.08);padding:5px 10px;border-radius:4px}
.eticket-admit{font-family:"IBM Plex Mono",monospace;font-size:10px;letter-spacing:.28em;text-transform:uppercase;color:var(--tk-gold2);margin-top:12px}
.eticket-status{font-family:"IBM Plex Mono",monospace;font-size:9px;letter-spacing:.12em;text-transform:uppercase;margin-top:6px;color:var(--tk-sage)}
.eticket-status.in{color:#16B47A}.eticket-status.void{color:#f0a3a0}
@media(max-width:560px){.eticket{flex-direction:column}.eticket-stub{width:auto;flex:auto;border-left:none;border-top:2px dashed rgba(159,179,168,.4)}
  .eticket-stub::before{top:-9px;left:auto;right:-9px}.eticket-stub::after{bottom:auto;top:-9px;left:-9px}}
@media print{body *{visibility:hidden}.eticket-print,.eticket-print *{visibility:visible}.eticket-print{position:absolute;left:0;top:0}}
</style>
