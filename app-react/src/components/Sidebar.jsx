import { motion, AnimatePresence } from 'framer-motion'
const groups = [
  { title:'Übersicht', items:[['dashboard','Dashboard','📊']] },
  { title:'Business', items:[['ads','Google Ads','📈'],['lex','Finanzen','💰'],['team','Team','👥'],['activity','Aktivität','📋']] },
  { title:'Kunden', items:[['leads','Anfragen','📥'],['web','Website','🌐']] },
]
export default function Sidebar({ active, go, open, setOpen }) {
  return (
    <>
      <AnimatePresence>
        {open && <motion.div initial={{opacity:0}} animate={{opacity:1}} exit={{opacity:0}}
          onClick={()=>setOpen(false)} className="fixed inset-0 bg-black/50 z-30 md:hidden" />}
      </AnimatePresence>
      <motion.aside
        className="fixed md:static z-40 h-full w-64 glass border-r border-line p-4 flex flex-col"
        initial={false}
        animate={{ x: (open || window.innerWidth>=768) ? 0 : -270 }}
        transition={{ type:'spring', stiffness:300, damping:30 }}>
        <div className="flex items-center gap-2 mb-5">
          <div className="text-2xl font-light tracking-[6px] text-white" style={{textShadow:'0 0 14px rgba(57,214,255,.5)'}}>OH</div>
          <div className="text-[8px] tracking-[3px] text-accent mt-1">BÜRO · ONLINE</div>
        </div>
        <input placeholder="Suchen…" className="w-full bg-[#0a0f1a] border border-line rounded-xl px-3 py-2 text-sm text-txt outline-none focus:border-accent mb-4" />
        <nav className="flex-1 overflow-y-auto">
          {groups.map(g=>(
            <div key={g.title} className="mb-4">
              <div className="text-[10px] uppercase tracking-wider text-muted mb-1 px-2">{g.title}</div>
              {g.items.map(([id,label,ic])=>(
                <button key={id} onClick={()=>{go(id);setOpen(false)}}
                  className={'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm mb-1 transition '+
                    (active===id?'bg-accent/15 text-accent font-semibold':'text-txt hover:bg-white/5')}>
                  <span>{ic}</span>{label}
                </button>
              ))}
            </div>
          ))}
        </nav>
        <div className="border-t border-line pt-3">
          <button onClick={()=>go('settings')} className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-txt hover:bg-white/5"><span>⚙️</span>Einstellungen</button>
          <a href="/buero.php?logout=1" className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-bad hover:bg-white/5"><span>⎋</span>Abmelden</a>
        </div>
      </motion.aside>
    </>
  )
}
