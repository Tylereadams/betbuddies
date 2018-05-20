from PIL import Image
from numpy import array
import csv
import os
import numpy
import sys

# Returns data about the file given
def getFileData(filePath):

	# convert image to 8-bit grayscale
	img = Image.open(filePath).convert('L')
	WIDTH, HEIGHT = img.size
	img = img.resize([400,400],Image.ANTIALIAS)
	data = list(img.getdata()) # convert image data to a list of integers
	# convert that to 2D list (list of lists of integers)
	data = [data[offset:offset+WIDTH] for offset in range(0, WIDTH*HEIGHT, WIDTH)]

	arr = array(img)

	imgValues = []
	for innerArrays in arr:
		for val in innerArrays:
			imgValues.append(val)

	return imgValues

def getDirectoryData(path):
	listing = os.listdir(path)
	directoryData = []
	for fileName in listing:
		# Skip hidden files
		if not fileName.startswith('.'):
			imgValues = getFileData(path + fileName)
			directoryData.append(imgValues)

	return directoryData

# Change the directory to this script's path
os.chdir(os.path.dirname(os.path.abspath(__file__)))

# Path to data folder for league
leagueDataDirectory = os.getcwd() + '/data/' + sys.argv[1]

goodTestValues = getDirectoryData(leagueDataDirectory + '/good/')
badTestValues = getDirectoryData(leagueDataDirectory + '/bad/')

#Assuming res is a flat list
with open(leagueDataDirectory + "/good_test_values.csv", "w") as output:
    writer = csv.writer(output, lineterminator='\n')
    for val in goodTestValues:
        writer.writerow([val])

#Assuming res is a flat list
with open(leagueDataDirectory + "/bad_test_values.csv", "w") as output:
    writer = csv.writer(output, lineterminator='\n')
    for val in badTestValues:
        writer.writerow([val])

numpy.savetxt(leagueDataDirectory + "/good_test_values.csv", goodTestValues, delimiter=",", fmt='%.1e')
numpy.savetxt(leagueDataDirectory + "/bad_test_values.csv", badTestValues, delimiter=",", fmt='%.1e')
