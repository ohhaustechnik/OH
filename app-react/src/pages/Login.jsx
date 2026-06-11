import { useState } from 'react'
import { motion } from 'framer-motion'
import { login } from '../api.js'
export default function Login({ onOk }) {
  const [pw,setPw]=useState(''); const [err,setErr]=useState(false); const [busy,setBusy]=useState(false)
  const submit=async(e)=>{ e.preventDefault(); setBusy(true); setErr(false)
    try{ const r=await login(pw); if(r&&r.ok) onOk(); else setErr(true) }catch{ setErr(true) } setBusy(false) }
  return (
    <div className="min-h-full grid place-items-center p-6">
      <motion.form onSubmit={submit} initial={{opacity:0,scale:.95,y:20}} animate={{opacity:1,scale:1,y:0}}
        transition={{duration:.5}} className="glass glow rounded-2xl p-9 w-full max-w-sm text-center">
        <div className="text-4xl font-light tracking-[10px] text-white mb-1" style={{textShadow:'0 0 22px rgba(57,214,255,.6)'}}>OH</div>
        <div className="text-[9px] tracking-[5px] text-accent mb-7">BÜRO · ZUGANG</div>
        <input type="password" value={pw} onChange={e=>setPw(e.target.value)} placeholder="• • • •" autoFocus
          className="w-full text-center text-xl tracking-[6px] bg-[#0a0f1a] border border-line rounded-xl px-4 py-3 text-txt outline-none focus:border-accent mb-4"/>
        <button disabled={busy} className="w-full py-3 rounded-xl font-bold text-bg"
          style={{background:'linear-gradient(140deg,#39d6ff,#1693c4)',boxShadow:'0 0 18px rgba(57,214,255,.4)'}}>
          {busy?'…':'Authentifizieren'}</button>
        {err && <motion.div initial={{opacity:0}} animate={{opacity:1}} className="mt-4 text-bad text-sm">Zugang verweigert.</motion.div>}
      </motion.form>
    </div>
  )
}
