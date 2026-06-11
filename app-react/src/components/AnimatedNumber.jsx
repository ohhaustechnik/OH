import { useEffect, useState } from 'react'
export default function AnimatedNumber({ value=0, format=(v)=>Math.round(v), dur=900 }) {
  const [n,setN]=useState(0)
  useEffect(()=>{ let s=null,raf; const a=0,b=Number(value)||0
    const step=(t)=>{ if(!s)s=t; const p=Math.min((t-s)/dur,1); setN(a+(b-a)*(1-Math.pow(1-p,3))); if(p<1)raf=requestAnimationFrame(step) }
    raf=requestAnimationFrame(step); return ()=>cancelAnimationFrame(raf) },[value,dur])
  return <>{format(n)}</>
}
