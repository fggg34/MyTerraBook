const ICONS = {
  search: <><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></>,
  arrow: <><path d="M5 12h14" /><path d="m13 6 6 6-6 6" /></>,
  chevron: <path d="m9 6 6 6-6 6" />,
  chevronLeft: <path d="m15 6-6 6 6 6" />,
  chevronDown: <path d="m6 9 6 6 6-6" />,
  plus: <><path d="M12 5v14" /><path d="M5 12h14" /></>,
  minus: <path d="M5 12h14" />,
  grid: <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>,
  card: <><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" /></>,
  shield: <path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3z" />,
  route: <><circle cx="6" cy="19" r="2" /><circle cx="18" cy="5" r="2" /><path d="M8 19h4a4 4 0 0 0 4-4V9a4 4 0 0 1 4-4" /></>,
  headset: <><path d="M4 13a8 8 0 0 1 16 0" /><path d="M4 13v4a2 2 0 0 0 2 2h1v-6H6a2 2 0 0 0-2 2z" /><path d="M20 13v4a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2z" /><path d="M20 19a3 3 0 0 1-3 2h-2" /></>,
  phone: <path d="M6 4h3l2 4-2 1a12 12 0 0 0 6 6l1-2 4 2v3a2 2 0 0 1-2 2A16 16 0 0 1 4 6a2 2 0 0 1 2-2z" />,
  mail: <><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m4 7 8 6 8-6" /></>,
  clock: <><circle cx="12" cy="12" r="8" /><path d="M12 8v5l3 2" /></>,
  book: <><path d="M12 7a8 8 0 0 0-8 0v12a8 8 0 0 1 8 0 8 8 0 0 1 8 0V7a8 8 0 0 0-8 0z" /><path d="M12 7v12" /></>,
  pin: <><path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z" /><circle cx="12" cy="10" r="2.5" /></>,
  user: <><circle cx="12" cy="8" r="3.5" /><path d="M5 19a7 7 0 0 1 14 0" /></>,
  calendar: <><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M3 10h18M8 3v4M16 3v4" /></>,
  download: <><path d="M12 4v10" /><path d="m8 10 4 4 4-4" /><path d="M5 19h14" /></>,
  file: <><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" /><path d="M14 3v5h5" /></>,
  info: <><circle cx="12" cy="12" r="8" /><path d="M12 11v5" /><path d="M12 8h.01" /></>,
  lock: <><rect x="5" y="11" width="14" height="9" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" /></>,
  instagram: <><rect x="4" y="4" width="16" height="16" rx="4" /><circle cx="12" cy="12" r="3.5" /><circle cx="17.5" cy="6.5" r="0.8" fill="currentColor" stroke="none" /></>,
  facebook: <path d="M14 8h2V5h-2c-2.2 0-4 1.8-4 4v2H8v3h2v6h3v-6h2.2l.8-3H13V9c0-.6.4-1 1-1z" />,
  snowflake: <><path d="M12 3v18" /><path d="m5 7 14 10" /><path d="m19 7-14 10" /><path d="M8 5l4 4 4-4" /><path d="M8 19l4-4 4 4" /></>,
  zap: <path d="M13 3 5 14h7l-1 7 8-11h-7l1-7z" />,
  wifi: <><path d="M5 12a10 10 0 0 1 14 0" /><path d="M8 15a6 6 0 0 1 8 0" /><path d="M12 18h.01" /></>,
  bath: <><path d="M4 13h16v2a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-2z" /><path d="M6 13V7a2 2 0 0 1 2-2h1" /><path d="M6 19v2M18 19v2" /></>,
  heart: <path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 4.6-7 9-7 9z" />,
  tent: <><path d="M4 20 12 5l8 15" /><path d="M9 20v-5h6v5" /><path d="M3 20h18" /></>,
  sun: <><circle cx="12" cy="12" r="4" /><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4" /></>,
  sparkles: <><path d="M12 4v4M12 16v4M4 12h4M16 12h4" /><path d="m7 7 2 2M15 15l2 2M17 7l-2 2M9 15l-2 2" /></>,
  house: <><path d="m4 11 8-7 8 7" /><path d="M6 10v9h12v-9" /><path d="M10 19v-5h4v5" /></>,
  map: <><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3z" /><path d="M9 3v15M15 6v15" /></>,
  star: <path d="m12 3 2.4 5.2L20 9.2l-4 3.8.9 5.5L12 16.8 7.1 18.5 8 13 4 9.2l5.6-1z" />,
  link: <><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1" /><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1" /></>,
}

export default function StrokeIcon({ name, size = 16 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      {ICONS[name] || null}
    </svg>
  )
}
