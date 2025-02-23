import { BrowserRouter, Route, Routes } from "react-router-dom";
import Layout from "./layouts/Layout";
import MainPage from "../pages/MainPage";

const Routers = () => {

    return <>
            <BrowserRouter>
                <Routes>
                    <Route element={<Layout />} >
                        <Route index element={<MainPage />}/>
                    </Route>
                </Routes>
            </BrowserRouter>
        </>
}

export default Routers
