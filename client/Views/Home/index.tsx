import React, { useContext, useEffect, useState } from 'react';
import { getRandomAlbums, Album } from '../../logic/Album';
import AlbumDisplay from '../components/AlbumDisplay';
import { User } from '../../logic/User';
import AmpacheError from '../../logic/AmpacheError';
import { MusicContext } from '../../Contexts/MusicContext';
import { playSongFromAlbum } from '../../Helpers/playAlbumHelper';
import { Song } from '../../logic/Song';

interface HomeViewProps {
    user: User;
}

const HomeView: React.FC<HomeViewProps> = (props) => {
    const musicContext = useContext(MusicContext);

    const [randomAlbums, setRandomAlbums] = useState<Album[]>([]);
    const [error, setError] = useState<Error | AmpacheError>(null);

    useEffect(() => {
        getRandomAlbums(props.user.username, 6, props.user.authKey)
            .then((albums: Album[]) => {
                setRandomAlbums(albums);
            })
            .catch((error) => {
                setError(error);
            });
    }, []);

    if (error) {
        return (
            <div className='albumPage'>
                <span>Error: {error.message}</span>
            </div>
        );
    }
    return (
        <div className='homePage'>
            <section>
                <h1>Random Albums</h1>
                <div className='randomAlbums'>
                    {randomAlbums.map((theAlbum) => {
                        return (
                            <AlbumDisplay
                                album={theAlbum}
                                playSongFromAlbum={(albumID, random) => {
                                    playSongFromAlbum(
                                        albumID,
                                        random,
                                        props.user.authKey,
                                        musicContext
                                    );
                                }}
                                key={theAlbum.id}
                            />
                        );
                    })}
                </div>
            </section>
            <section>
                <span>{musicContext.songQueueIndex}</span>
                {musicContext.songQueue.map((song: Song) => {
                    return (
                        <div
                            key={song.id}
                            style={
                                musicContext.currentPlayingSong?.id === song.id
                                    ? { color: 'orange' }
                                    : {}
                            }
                        >
                            {song.title}
                        </div>
                    );
                })}
            </section>
        </div>
    );
};

export default HomeView;
