import { createRoot } from 'react-dom/client'
import Routers from './app/Routers'
import '../css/app.css'

createRoot(document.getElementById('root')!).render(
    <Routers />
)
