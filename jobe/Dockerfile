##############################################################################################################################
# Custom dockerfile for the Jobe-server with the python module "requests" installed, 
# the number of simultaneous jobs and the correct timezone set.
#
# Build image with this command:
# docker build . -t etutorplusplus/jobeinabox --build-arg TZ="Europe/Vienna" [--build-arg njobs=16]
# Notes: 	The build-arg njobs defines the number of simultaneous jobs the server will allow.
#		You can override this value by setting the SIM_JOBE_JOBS environment variable for the docker-container.
#
#		The base image from trampgeek offers only a latest version. To make it more specific, the current image as of
#		08/2023 is referenced by the digest. Update or remove the digest to base this image off the latest version.
###############################################################################################################################

# Use the "official" image as the base
FROM trampgeek/jobeinabox:latest@sha256:0d71ab80619f62efe3ed170048dd862100c2e457484a3fa77c00765d77427b9c

# Number of simultaneous jobs the jobe server will allow 
ARG njobs=8
ENV SIM_JOBE_JOBS=${njobs}

# Install the python requests module
RUN pip install requests

# Add the entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]