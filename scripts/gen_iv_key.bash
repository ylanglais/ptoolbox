[ $# = 1 ] && { 
	pass=$1
} || {
	pass=$(openssl rand -base64 25)
}
#echo pass=$pass
openssl enc -nosalt -aes-256-cbc -k "$pass" -P -pbkdf2

