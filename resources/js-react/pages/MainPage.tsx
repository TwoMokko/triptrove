import api from '../app/api/api';
import { useEffect, useState } from 'react';
import { travelData } from "../app/types/types";

const MainPage = () => {
    const [travels, setTravels] = useState<travelData[]>([])
    const [inputs, setInputs] = useState<travelData>()

    useEffect(() => {
        getTravels().then(() => console.log({travels}))
    }, [])

    const getTravels = async () => {
        try {
            const response = await api.get('/travels')
            setTravels(response.data)
        } catch (error) {
            console.error('Error fetching travels:', error)
        }
    }

    // @ts-ignore
    const createTravel = async (data: travelData) => {
        console.log({data})

        try {
            const response = await api.post('/travels', data)
            console.log('Travel created:', response.data)
            getTravels().then(() => console.log({response}))
        } catch (error) {
            console.error('Error creating travel:', error)
        }
    }

    return <div className='mx-20 py-6'>
        <div className='border-blue-600 border-2 border-solid rounded-md'>
            {
                travels.map(itm => <div key={itm.id} className='flex gap-5 p-2'>
                    <div>{itm.place}</div>
                    <div>{itm.date}</div>
                    <div>{itm.mode_of_transport}</div>
                    <div>{itm.good_impression}</div>
                    <div>{itm.bad_impression}</div>
                    <div>{itm.good_impression}</div>
                </div>)
            }
        </div>
        <div className='flex flex-col gap-5 py-6'>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='place' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, place: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='date' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, date: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='mode_of_transport' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, mode_of_transport: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='good_impression' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, good_impression: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='bad_impression' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, bad_impression: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='general_impression' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, general_impression: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='user_id' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, user_id: 1}
                })
            }}/>
        </div>
        <button className='bg-blue-600 text-white rounded-md py-2 px-4' onClick={() => createTravel(inputs)}>add</button>
    </div>
}

export default MainPage;
