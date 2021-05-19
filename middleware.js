exports.main = function(socket, next)
{
    if (
        typeof socket.handshake.query.type === typeof undefined ||
        typeof socket.handshake.query.name === typeof undefined
    ) {
        return next(new Error('Something went wrong'));
    } else {
        next();
    }
}