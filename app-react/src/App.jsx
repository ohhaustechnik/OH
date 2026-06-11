import { useEffect, useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { api } from './api.js'
import Sidebar from './components/Sidebar.jsx'
import Login from './pages/Login.jsx'
import Dashboard from './pages/Dashboard.jsx'

function Stub({ title, note }) {
  return (
    <div className="p-6 max-w-3xl mx-auto">
      <motion.div initial={{opacity:0,y:16}} animate={{opacity:1,y:0}} className="glass rounded-2xl p-6">
        <h2 className="text-xl font-bold text-white">{title}</h2>
        <p className="text-muted mt-2 text-sm">{note}</p>
        <a href="/buero.php" className="inline-block mt-4 px-4 py-2 rounded-xl text-bg font-semibold"
           style={{background:'linear-gradient(140deg,#39d6ff,#1693c4)'}}>Im klassischen Büro öffnen →</a>
      </motion.div>
    </div>
  )
}

export default function App() {
  const [auth,setAuth]=useState(null)   // null=prüfen, false=login, true=drin
  const [data,setData]=useState(null)
  const [page,setPage]=useState('dashboard')
  const [open,setOpen]=useState(false)

  const loadDash=async()=>{ try{ const d=await api('dashboard'); if(d&&d.stats){ setData(d); setAuth(true); return true } }catch{} return false }
  useEffect(()=>{ (async()=>{ const ok=await loadDash(); if(!ok) setAuth(false) })() },[])

  if(auth===null) return <div className="min-h-full grid place-items-center text-muted">Lade …</div>
  if(auth===false) return <Login onOk={async()=>{ await loadDash() }} />

  const pages = {
    dashboard: <Dashboard data={data}/>,
    ads: <Stub title="📈 Google Ads" note="Wird gerade nach React portiert. Bis dahin im klassischen Büro voll nutzbar."/>,
    lex: <Stub title="💰 Finanzen / Lexware" note="Aylins Rechnungen – Portierung folgt."/>,
    team: <Stub title="👥 Team" note="Deine KI-Agenten – Portierung folgt."/>,
    activity: <Stub title="📋 Aktivität" note="Aktivitäts-Protokoll – Portierung folgt."/>,
    leads: <Stub title="📥 Anfragen" note="Lead-Verwaltung – Portierung folgt."/>,
    web: <Stub title="🌐 Website" note="Dilara Website-Optimierung – Portierung folgt."/>,
    settings: <Stub title="⚙️ Einstellungen" note="Verbindungen & Zugänge – Portierung folgt."/>,
  }

  return (
    <div className="flex h-full">
      <Sidebar active={page} go={setPage} open={open} setOpen={setOpen}/>
      <div className="flex-1 flex flex-col min-w-0">
        <header className="glass border-b border-line px-4 py-3 flex items-center gap-3 sticky top-0 z-20">
          <button onClick={()=>setOpen(true)} className="md:hidden text-accent text-xl">☰</button>
          <div className="text-sm text-muted">OH Haustechnik · Büro</div>
          <div className="ml-auto text-[11px] text-accent flex items-center gap-1">
            <span className="w-2 h-2 rounded-full bg-ok inline-block"/>KI aktiv</div>
        </header>
        <main className="flex-1 overflow-y-auto">
          <AnimatePresence mode="wait">
            <motion.div key={page} initial={{opacity:0,y:10}} animate={{opacity:1,y:0}} exit={{opacity:0,y:-10}} transition={{duration:.25}}>
              {pages[page]}
            </motion.div>
          </AnimatePresence>
        </main>
      </div>
    </div>
  )
}
