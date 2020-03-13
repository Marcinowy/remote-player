exports.main = function(socket, next)
{
    if (typeof socket.handshake.query.type === 'undefined' || typeof socket.handshake.query.name === 'undefined') {
        return next(new Error('Something went wrong'));
    } else {
        next();
    }
}