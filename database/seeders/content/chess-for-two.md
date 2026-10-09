## The idea

Most online chess requires accounts, servers, and a database. This project asks a smaller question: can two people play a full game of chess from a single web page, connected directly browser to browser?

[[architecture]]

## Connecting two browsers

The game uses **PeerJS**, a library on top of WebRTC. One player hosts a game and shares an invite link; the other opens it and joins using the host's peer ID. PeerJS's signalling service brokers the initial connection, after which moves and chat messages travel over a WebRTC data connection between the two browsers. There is no application backend and no database.

## A rules engine in plain JavaScript

Move validation is written from scratch, without a chess library:

- **Pseudo-legal moves first.** Each piece generates its candidate moves.
- **Then legality.** Any move that would leave the player's own king under attack is discarded, using an "is this square attacked?" check.
- **Special moves.** Castling (with castling rights tracked through the game), en passant, and pawn promotion with a choice of piece.
- **Game end.** Checkmate and stalemate are detected when a player has no legal moves.

## Keeping both boards in sync

Each move is sent to the opponent over the data connection and applied to their board, so both players see the same position. The same channel carries in-game chat messages. Moves can be made by clicking or by drag-and-drop.

## Limitations

- **Restrictive networks.** Peer-to-peer connections can fail where WebRTC can't traverse NAT without a relay (TURN) server.
- **No persistence.** With no database, refreshing the page ends the game.
- **Third-party signalling.** Connection setup relies on the PeerJS public signalling service.
