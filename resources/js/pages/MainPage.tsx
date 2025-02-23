import api from '../app/api/api';
import { useEffect, useState } from 'react';

const MainPage = () => {
    const [travels, setTravels] = useState([])
    const [inputs, setInputs] = useState<{ title: string, content: string }>({title: 'test', content: 'test'})

    useEffect(() => {
        getTravels()
    }, [])

    const getTravels = () => {
        // @ts-ignore
        const fetchPosts = async () => {
            try {
                const response = await api.get('/travels')
                setTravels(response.data)
            } catch (error) {
                console.error('Error fetching posts:', error)
            }
        };

        fetchPosts().then(() => console.log({travels}))
    }

    // @ts-ignore
    const createPost = async (title: string, content: string) => {
        try {
            const response = await api.post('/travels', { title, content })
            console.log('Post created:', response.data)
            getTravels()
        } catch (error) {
            console.error('Error creating post:', error)
        }
    }

    return <div className='mx-20 py-6'>
        <div className='border-blue-600 border-2 border-solid rounded-md'>
            {
                travels.map(itm => <div key={itm.id} className='flex gap-5 p-2'>
                    <div>{itm.title}</div>
                    <div>{itm.content}</div>
                </div>)
            }
        </div>
        <div className='flex gap-5 py-6'>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='title' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, title: val}
                })
            }}/>
            <input className='p-2 border-gray-200 border-2 border-solid rounded-md' placeholder='content' onBlur={(event) => {
                const val = event.currentTarget.value
                setInputs(prev => {
                    return {...prev, content: val}
                })
            }}/>
        </div>
        <button className='bg-blue-600 text-white rounded-md py-2 px-4' onClick={() => createPost(inputs.title, inputs.content)}>add</button>
    </div>
}

export default MainPage;
