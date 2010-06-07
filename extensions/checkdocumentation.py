import os
import re

allowed = ['Integer','Boolean','String','PageVO','Array','ParserOptions','Parser','Unknown','ADOConnection',
'PresentationVO','Title','MwParser','SurveyButtons','ChoiceVO','VoteVO','SurveyVO','GraphValues','MwUser']

def checkValid(file):
	print file
	for num,line in enumerate(open(file)):
		if "@param" in line:
			line2 = re.sub("^\s+", "", line)
			assert( line2.startswith("* @param ") )
			line2 = re.sub("^\* @param ", '',line2)
			elem = line2.strip().split(' ')
			if len(elem) < 2:
				print num,line,
				assert(False)
			elif elem[0] not in allowed:
				print num,line,
				print ">"+elem[0]+"."
				assert(False)
			assert(elem[1].startswith('$'))
		if "@return" in line:
			line2 = re.sub("^\s+", "", line)
			assert( line2.startswith("* @return ") )
			line2 = re.sub("^\* @return ", '',line2)
			elem = line2.strip().split(' ')
			if len(elem) < 1:
				print num,line,
				assert(False)
			elif elem[0] not in allowed:
				print num,line,
				assert(False)

top = "c:\\xampp\\htdocs\\new\\extensions\\votapedia"
for root, dirs, files in os.walk(top, topdown=False):
    for name in files:
		if name.endswith('.php'):
			pth =  os.path.join(root, name)
			checkValid( pth )

